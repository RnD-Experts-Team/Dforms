<?php

namespace App\Http\Controllers;

use App\Services\LanguageService;
use App\Http\Requests\Language\StoreLanguageRequest;
use App\Http\Requests\Language\UpdateLanguageRequest;
use App\Http\Requests\Language\SetDefaultLanguageRequest;
use Illuminate\Http\JsonResponse;

class LanguageController extends Controller
{
    protected LanguageService $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * Get all languages
     * GET /api/languages
     */
    public function index(): JsonResponse
    {
        try {
            $languages = $this->languageService->getAllLanguages();

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
     * Create a new language
     * POST /api/languages
     */
    public function store(StoreLanguageRequest $request): JsonResponse
    {
        try {
            $language = $this->languageService->createLanguage($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Language created successfully.',
                'data' => $language,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create language.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing language
     * PUT /api/languages/{id}
     */
    public function update(UpdateLanguageRequest $request, int $id): JsonResponse
    {
        try {
            $language = $this->languageService->updateLanguage($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Language updated successfully.',
                'data' => $language,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update language.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set a language as default
     * POST /api/languages/set-default
     */
    public function setDefault(SetDefaultLanguageRequest $request): JsonResponse
    {
        try {
            $language = $this->languageService->setDefaultLanguage($request->validated()['language_id']);

            return response()->json([
                'success' => true,
                'message' => 'Default language set successfully.',
                'data' => $language,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set default language.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a language
     * DELETE /api/languages/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->languageService->deleteLanguage($id);

            return response()->json([
                'success' => true,
                'message' => 'Language deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete language.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
