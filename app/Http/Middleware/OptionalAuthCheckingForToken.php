<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class OptionalAuthCheckingForToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');
        
        // If no auth header, continue as guest
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $next($request);
        }

        $userToken = trim(substr($authHeader, 7));
        
        // Empty token - continue as guest
        if (empty($userToken)) {
            return $next($request);
        }

        $cfg = config('services.auth_server');
        $url = rtrim($cfg['base_url'], '/') . '/' . ltrim($cfg['verify_path'], '/');

        // context keys for caching
        $routeName = $request->route()?->getName();
        $ctxKey    = $routeName ?: ($request->method() . ' ' . $request->getPathInfo());
        $cacheKey  = 'verify:v1:' . hash('sha256', $userToken) . ':' . md5($ctxKey) . ':' . ($cfg['service_name'] ?? 'svc');

        if ($cached = Cache::get($cacheKey)) {
            return $this->apply($cached, $request, $next);
        }

        $http = Http::acceptJson()
            ->timeout($cfg['timeout'])
            ->retry($cfg['retries'], $cfg['retry_ms'])
            ->withToken($cfg['call_token']);

        $payload = [
            'service'    => $cfg['service_name'],
            'token'      => $userToken,
            'method'     => $request->method(),
            'path'       => $request->getPathInfo(),
            'route_name' => $routeName,
        ];

        try {
            $resp = $http->post($url, $payload);
        } catch (\Throwable $e) {
            // If auth service is down, continue as guest instead of blocking
            return $next($request);
        }

        // If token is invalid, continue as guest
        if (!$resp->ok()) {
            return $next($request);
        }

        $data = $resp->json();

        // If token is inactive, continue as guest
        if (!($data['active'] ?? false)) {
            return $next($request);
        }

        // NOTE: We don't check authorization for optional auth
        // The route itself will determine if the user has sufficient access

        $userArr = (array) ($data['user'] ?? []);
        if (!array_key_exists('id', $userArr)) {
            // Invalid user data - continue as guest
            return $next($request);
        }

        // Sync user, roles, and permissions to local database
        $this->syncAuthData($userArr, $data['roles'] ?? [], $data['permissions'] ?? []);

        // Set the authenticated user from our local database
        $localUser = User::find($userArr['id']);
        Auth::setUser($localUser);

        // cache
        $ttl = max(1, (int) ($cfg['cache_ttl'] ?? 30));
        if (isset($data['exp']) && is_int($data['exp'])) {
            $secondsLeft = $data['exp'] - time();
            if ($secondsLeft > 0) $ttl = min($ttl, $secondsLeft);
        }
        Cache::put($cacheKey, $data, now()->addSeconds($ttl));

        return $next($request);
    }

    private function apply(array $data, Request $request, Closure $next): Response
    {
        // If token is inactive, continue as guest
        if (!($data['active'] ?? false)) {
            return $next($request);
        }

        // NOTE: We don't check authorization for optional auth

        $userArr = (array) ($data['user'] ?? []);
        
        if (!array_key_exists('id', $userArr)) {
            return $next($request);
        }

        // Sync and set authenticated user
        $this->syncAuthData($userArr, $data['roles'] ?? [], $data['permissions'] ?? []);
        $localUser = User::find($userArr['id']);
        Auth::setUser($localUser);

        return $next($request);
    }

    /**
     * Sync user, roles, and permissions from auth system to local database
     */
    private function syncAuthData(array $userArr, array $rolesArr, array $permissionsArr): void
    {
        DB::transaction(function () use ($userArr, $rolesArr, $permissionsArr) {
            // 1. Sync or create user
            $user = User::updateOrCreate(
                ['id' => $userArr['id']],
                [
                    'name' => $userArr['name'] ?? '',
                    'email' => $userArr['email'] ?? '',
                ]
            );

            // 2. Sync roles
            $roleIds = [];
            foreach ($rolesArr as $roleData) {
                if (is_array($roleData) && isset($roleData['id'])) {
                    $role = Role::updateOrCreate(
                        ['id' => $roleData['id']],
                        [
                            'name' => $roleData['name'] ?? '',
                            'description' => $roleData['description'] ?? null,
                        ]
                    );
                    $roleIds[] = $role->id;
                }
            }

            // 3. Sync permissions
            $permissionIds = [];
            foreach ($permissionsArr as $permData) {
                if (is_array($permData) && isset($permData['id'])) {
                    $permission = Permission::updateOrCreate(
                        ['id' => $permData['id']],
                        [
                            'name' => $permData['name'] ?? '',
                            'description' => $permData['description'] ?? null,
                        ]
                    );
                    $permissionIds[] = $permission->id;
                }
            }

            // 4. Sync user's roles (remove old, add new)
            $user->roles()->sync($roleIds);

            // 5. Sync user's permissions (remove old, add new)
            $user->permissions()->sync($permissionIds);
        });
    }
}
