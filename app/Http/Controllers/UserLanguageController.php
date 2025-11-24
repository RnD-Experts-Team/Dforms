<?php

namespace App\Http\Controllers;

use App\Services\UserLanguageService;
use App\Http\Requests\User\SetDefaultLanguageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserLanguageController extends Controller
{
    protected UserLanguageService $userLanguageService;

    public function __construct(UserLanguageService $userLanguageService)
    {
        $this->userLanguageService = $userLanguageService;
    }

    /**
     * GET /api/user/language/all
     * Get all available languages for user selection
     */
    public function getAllLanguages(): JsonResponse
    {
        try {
            $languages = $this->userLanguageService->getAllLanguages();

            return response()->json([
                'success' => true,
                'data' => $languages,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve languages.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/user/language/default
     * Get current user's default language
     */
    public function getUserDefaultLanguage(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $language = $this->userLanguageService->getUserDefaultLanguage($userId);

            return response()->json([
                'success' => true,
                'data' => $language,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user default language.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * PUT /api/user/language/default
     * Set current user's default language
     */
    public function setUserDefaultLanguage(SetDefaultLanguageRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $user = $this->userLanguageService->setUserDefaultLanguage(
                $userId,
                $request->input('language_id')
            );

            return response()->json([
                'success' => true,
                'message' => 'Default language updated successfully.',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update default language.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
