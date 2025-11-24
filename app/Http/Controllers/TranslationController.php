<?php

namespace App\Http\Controllers;

use App\Services\TranslationService;
use App\Http\Requests\Translation\GetLocalizableDataRequest;
use App\Http\Requests\Translation\SaveTranslationsRequest;
use Illuminate\Http\JsonResponse;

class TranslationController extends Controller
{
    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * GET /api/translations/available-languages
     * Get all available languages for translation (excluding default)
     */
    public function getAvailableLanguages(): JsonResponse
    {
        try {
            $languages = $this->translationService->getAvailableLanguagesForTranslation();

            return response()->json([
                'success' => true,
                'data' => $languages,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available languages.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/translations/localizable-data
     * Get all localizable items for a form version in default language
     */
    public function getLocalizableData(GetLocalizableDataRequest $request): JsonResponse
    {
        try {
            $data = $this->translationService->getLocalizableData(
                $request->input('form_version_id'),
                $request->input('language_id')
            );

            return response()->json([
                'success' => true,
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve localizable data.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /api/translations/save
     * Save translations for a form version and language
     */
    public function saveTranslations(SaveTranslationsRequest $request): JsonResponse
    {
        try {
            $this->translationService->saveTranslations($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Translations saved successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save translations.',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
