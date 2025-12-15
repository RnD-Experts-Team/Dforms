<?php

namespace App\Services;

use App\Models\Stage;
use App\Models\User;
use App\Models\Entry;
use App\Models\Field;
use App\Models\EntryValue;

class StageAccessCheckService
{
    /**
     * Check if a user can access a specific stage
     * 
     * @param Stage $stage The stage to check access for
     * @param User|null $user The user attempting access (null for guest)
     * @param Entry|null $entry The entry being accessed (for email field matching)
     * @return bool
     */
    public function canUserAccessStage(Stage $stage, ?User $user, ?Entry $entry = null): bool
    {
        // Load access rule if not already loaded
        if (!$stage->relationLoaded('accessRule')) {
            $stage->load('accessRule');
        }

        $accessRule = $stage->accessRule;

        // CHANGED: If no access rule exists, allow access (public by default)
        if (!$accessRule) {
            return true;
        }

        // CHANGED: Check if access rule has any restrictions at all
        $hasRestrictions = $accessRule->allow_authenticated_users
            || !empty($accessRule->allowed_users)
            || !empty($accessRule->allowed_roles)
            || !empty($accessRule->allowed_permissions)
            || $accessRule->email_field_id;

        // If no restrictions are set, allow everyone (including guests)
        if (!$hasRestrictions) {
            return true;
        }

        // From here on, we have restrictions - check them in order

        // If guest user but restrictions exist, deny access
        if (!$user) {
            return false;
        }

        // Check authenticated users flag
        if ($accessRule->allow_authenticated_users) {
            return true;
        }

        // Check specific users - properly handle JSON string from database
        if (!empty($accessRule->allowed_users)) {
            $allowedUsers = is_array($accessRule->allowed_users) 
                ? $accessRule->allowed_users 
                : json_decode($accessRule->allowed_users, true) ?? [];

            if (in_array($user->id, $allowedUsers)) {
                return true;
            }
        }

        // Check roles - properly handle JSON string from database
        if (!empty($accessRule->allowed_roles)) {
            $allowedRoles = is_array($accessRule->allowed_roles)
                ? $accessRule->allowed_roles
                : json_decode($accessRule->allowed_roles, true) ?? [];

            $userRoleIds = $user->roles()->pluck('roles.id')->toArray();

            if (!empty(array_intersect($allowedRoles, $userRoleIds))) {
                return true;
            }
        }

        // Check permissions - properly handle JSON string from database
        if (!empty($accessRule->allowed_permissions)) {
            $allowedPermissions = is_array($accessRule->allowed_permissions)
                ? $accessRule->allowed_permissions
                : json_decode($accessRule->allowed_permissions, true) ?? [];

            $userPermissionIds = $user->permissions()->pluck('permissions.id')->toArray();

            if (!empty(array_intersect($allowedPermissions, $userPermissionIds))) {
                return true;
            }
        }

        // Check email field matching
        if ($accessRule->email_field_id && $entry) {
            if ($this->checkEmailFieldMatch($accessRule->email_field_id, $user, $entry)) {
                return true;
            }
        }

        // If restrictions exist but none match, deny access
        return false;
    }

    /**
     * Check if user's email matches the email stored in a specific field
     * 
     * @param int $emailFieldId
     * @param User $user
     * @param Entry $entry
     * @return bool
     */
    private function checkEmailFieldMatch(int $emailFieldId, User $user, Entry $entry): bool
    {
        // Get the email field
        $emailField = Field::find($emailFieldId);
        if (!$emailField) {
            return false;
        }

        // Verify it's actually an email field type
        if (!$emailField->relationLoaded('fieldType')) {
            $emailField->load('fieldType');
        }

        if ($emailField->fieldType->name !== 'Email Input') {
            return false;
        }

        // Get the entry value for this field
        $entryValue = EntryValue::where('entry_id', $entry->id)
            ->where('field_id', $emailFieldId)
            ->first();

        if (!$entryValue) {
            return false;
        }

        // Compare emails (case-insensitive)
        return strtolower(trim($entryValue->value)) === strtolower(trim($user->email));
    }

    /**
     * Get all forms that are accessible by a user (for form list)
     * 
     * @param User|null $user
     * @return array Array of accessible form IDs
     */
    public function getAccessibleFormIds(?User $user): array
    {
        $accessibleFormIds = [];

        // Get all published form versions
        $publishedVersions = \App\Models\FormVersion::where('status', 'published')
            ->with(['stages' => function($query) {
                $query->where('is_initial', true)->with('accessRule');
            }, 'form'])
            ->get();

        foreach ($publishedVersions as $version) {
            $initialStage = $version->stages->first();

            // CHANGED: Only check access if initial stage exists
            // Allows access by default if stage is found
            if ($initialStage && $this->canUserAccessStage($initialStage, $user)) {
                $accessibleFormIds[] = $version->form_id;
            }
        }

        return array_unique($accessibleFormIds);
    }

    /**
     * Check if user can access a specific entry at its current stage
     * 
     * @param Entry $entry
     * @param User|null $user
     * @return bool
     */
    public function canUserAccessEntry(Entry $entry, ?User $user): bool
    {
        if (!$entry->relationLoaded('currentStage')) {
            $entry->load('currentStage');
        }

        return $this->canUserAccessStage($entry->currentStage, $user, $entry);
    }
}
