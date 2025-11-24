<?php

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\DB;

class LanguageService
{
    /**
     * Get all languages
     */
    public function getAllLanguages()
    {
        return Language::orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Create a new language
     */
    public function createLanguage(array $data)
    {
        DB::beginTransaction();

        try {
            // If is_default is true, set all other languages to false
            if (isset($data['is_default']) && $data['is_default']) {
                Language::where('is_default', true)->update(['is_default' => false]);
            }

            $language = Language::create($data);

            DB::commit();

            return $language;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing language
     */
    public function updateLanguage(int $id, array $data)
    {
        $language = Language::findOrFail($id);

        $language->update($data);

        return $language;
    }

    /**
     * Set a language as default
     */
    public function setDefaultLanguage(int $languageId)
    {
        DB::beginTransaction();

        try {
            // Set all languages to not default
            Language::where('is_default', true)->update(['is_default' => false]);

            // Set the selected language as default
            $language = Language::findOrFail($languageId);
            $language->update(['is_default' => true]);

            DB::commit();

            return $language;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a language
     */
    public function deleteLanguage(int $id)
    {
        $language = Language::findOrFail($id);

        // Prevent deletion of default language
        if ($language->is_default) {
            throw new \Exception('Cannot delete the default language. Please set another language as default first.');
        }

        // Check if language is being used by users
        $usersCount = $language->users()->count();
        if ($usersCount > 0) {
            throw new \Exception("Cannot delete this language. It is currently used by {$usersCount} user(s).");
        }

        // Check if language has translations
        $translationsCount = $language->formVersionTranslations()->count() + 
                            $language->fieldTranslations()->count();
        if ($translationsCount > 0) {
            throw new \Exception("Cannot delete this language. It has {$translationsCount} translation(s) associated with it.");
        }

        $language->delete();

        return true;
    }

    /**
     * Get default language
     */
    public function getDefaultLanguage()
    {
        return Language::where('is_default', true)->first();
    }
}
