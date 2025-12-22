<?php

namespace App\Services;

use App\Models\FormVersion;
use App\Models\Language;
use App\Models\FormVersionTranslation;
use App\Models\FieldTranslation;
use App\Models\StageTranslation;
use App\Models\SectionTranslation;
use App\Models\StageTransitionTranslation;
use Illuminate\Support\Facades\DB;

class TranslationService
{
    /**
     * Get all localizable data for a form version in specified language
     */
    public function getLocalizableData(int $formVersionId, int $languageId)
    {
        // Validate that the target language is NOT the default language
        $language = Language::findOrFail($languageId);

        if ($language->is_default) {
            throw new \Exception('Cannot create translations for the default language.');
        }

        $formVersion = FormVersion::with([
            'form',
            'stages.sections.fields',
            'stageTransitions',
            'translations' => function ($q) use ($languageId) {
                $q->where('language_id', $languageId);
            }
        ])->findOrFail($formVersionId);

        // Load translations for the specified language
        $formVersion->load([
            'stages.translations' => function ($q) use ($languageId) {
                $q->where('language_id', $languageId);
            },
            'stages.sections.translations' => function ($q) use ($languageId) {
                $q->where('language_id', $languageId);
            },
            'stages.sections.fields.translations' => function ($q) use ($languageId) {
                $q->where('language_id', $languageId);
            },
            'stageTransitions.translations' => function ($q) use ($languageId) {
                $q->where('language_id', $languageId);
            },
        ]);

        // Get existing form version translation
        $formTranslation = $formVersion->translations->first();
        $translatedFormName = $formTranslation ? $formTranslation->name : '';

        // Stage translations (NEW)
        $stages = [];
        foreach ($formVersion->stages as $stage) {
            $stageTranslation = $stage->translations->first();

            $stages[] = [
                'stage_id' => $stage->id,
                'original' => [
                    'name' => $stage->name,
                ],
                'translated' => [
                    'name' => $stageTranslation ? $stageTranslation->name : '',
                ],
            ];
        }

        // Section translations (NEW)
        $sections = [];
        foreach ($formVersion->stages as $stage) {
            foreach ($stage->sections as $section) {
                $sectionTranslation = $section->translations->first();

                $sections[] = [
                    'section_id' => $section->id,
                    'stage_id' => $stage->id,
                    'original' => [
                        'name' => $section->name,
                    ],
                    'translated' => [
                        'name' => $sectionTranslation ? $sectionTranslation->name : '',
                    ],
                ];
            }
        }

        // Transition label translations (NEW)
        $transitions = [];
        foreach ($formVersion->stageTransitions as $transition) {
            $transitionTranslation = $transition->translations->first();

            $transitions[] = [
                'stage_transition_id' => $transition->id,
                'original' => [
                    'label' => $transition->label,
                ],
                'translated' => [
                    'label' => $transitionTranslation ? $transitionTranslation->label : '',
                ],
            ];
        }

        // Get all fields from all stages/sections with their translations (EXISTING)
        $fields = [];
        foreach ($formVersion->stages as $stage) {
            foreach ($stage->sections as $section) {
                foreach ($section->fields as $field) {
                    // Get field translation if exists
                    $fieldTranslation = $field->translations->first();

                    $fields[] = [
                        'field_id' => $field->id,
                        // Original values (default language)
                        'original' => [
                            'label' => $field->label,
                            'helper_text' => $field->helper_text,
                            'default_value' => $field->default_value,
                            'placeholder' => $field->placeholder,
                        ],
                        // Translated values (target language)
                        'translated' => [
                            'label' => $fieldTranslation ? $fieldTranslation->label : '',
                            'helper_text' => $fieldTranslation ? $fieldTranslation->helper_text : '',
                            'default_value' => $fieldTranslation ? $fieldTranslation->default_value : '',
                            'place_holder' => $fieldTranslation ? $fieldTranslation->placeholder : '',
                        ],
                    ];
                }
            }
        }

        return [
            'form_version_id' => $formVersion->id,
            'language' => [
                'id' => $language->id,
                'code' => $language->code,
                'name' => $language->name,
                'is_default' => $language->is_default,
            ],
            // Form name in both original and translated
            'form_name' => [
                'original' => $formVersion->form->name,
                'translated' => $translatedFormName,
            ],

            // NEW keys (added without changing existing ones)
            'stages' => $stages,
            'sections' => $sections,
            'transitions' => $transitions,

            // existing
            'fields' => $fields,
        ];
    }

    /**
     * Save translations for a form version and language
     */
    public function saveTranslations(array $data)
    {
        DB::beginTransaction();

        try {
            $formVersionId = $data['form_version_id'];
            $languageId = $data['language_id'];

            // Validate that the target language is NOT the default language
            $language = Language::findOrFail($languageId);

            if ($language->is_default) {
                throw new \Exception('Cannot save translations for the default language.');
            }

            // Save or update form version translation (form name)
            if (isset($data['form_name'])) {
                FormVersionTranslation::updateOrCreate(
                    [
                        'form_version_id' => $formVersionId,
                        'language_id' => $languageId,
                    ],
                    [
                        'name' => $data['form_name'],
                    ]
                );
            }

            // NEW: Save or update stage translations
            if (isset($data['stage_translations'])) {
                foreach ($data['stage_translations'] as $stageTranslation) {
                    StageTranslation::updateOrCreate(
                        [
                            'stage_id' => $stageTranslation['stage_id'],
                            'language_id' => $languageId,
                        ],
                        [
                            'name' => $stageTranslation['name'] ?? '',
                        ]
                    );
                }
            }

            // NEW: Save or update section translations
            if (isset($data['section_translations'])) {
                foreach ($data['section_translations'] as $sectionTranslation) {
                    SectionTranslation::updateOrCreate(
                        [
                            'section_id' => $sectionTranslation['section_id'],
                            'language_id' => $languageId,
                        ],
                        [
                            'name' => $sectionTranslation['name'] ?? '',
                        ]
                    );
                }
            }

            // NEW: Save or update transition translations
            if (isset($data['transition_translations'])) {
                foreach ($data['transition_translations'] as $transitionTranslation) {
                    StageTransitionTranslation::updateOrCreate(
                        [
                            'stage_transition_id' => $transitionTranslation['stage_transition_id'],
                            'language_id' => $languageId,
                        ],
                        [
                            'label' => $transitionTranslation['label'] ?? '',
                        ]
                    );
                }
            }

            // Save or update field translations (existing)
            if (isset($data['field_translations'])) {
                foreach ($data['field_translations'] as $fieldTranslation) {
                    $fieldId = $fieldTranslation['field_id'];

                    // Save translation (allow empty strings to clear translations)
                    FieldTranslation::updateOrCreate(
                        [
                            'field_id' => $fieldId,
                            'language_id' => $languageId,
                        ],
                        [
                            'label' => $fieldTranslation['label'] ?? '',
                            'helper_text' => $fieldTranslation['helper_text'] ?? '',
                            'default_value' => $fieldTranslation['default_value'] ?? '',
                            'placeholder' => $fieldTranslation['place_holder'] ?? '',
                        ]
                    );
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Translations saved successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get all available languages (excluding default for translation purposes)
     */
    public function getAvailableLanguagesForTranslation()
    {
        return Language::where('is_default', false)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_default']);
    }

    /**
     * Get default language
     */
    public function getDefaultLanguage()
    {
        return Language::where('is_default', true)
            ->first(['id', 'code', 'name', 'is_default']);
    }

    /**
     * Delete translations for a specific language
     */
    public function deleteTranslations(int $formVersionId, int $languageId)
    {
        // Validate that the target language is NOT the default language
        $language = Language::findOrFail($languageId);

        if ($language->is_default) {
            throw new \Exception('Cannot delete translations for the default language.');
        }

        DB::beginTransaction();

        try {
            // Delete form version translation
            FormVersionTranslation::where('form_version_id', $formVersionId)
                ->where('language_id', $languageId)
                ->delete();

            // Load structure for IDs
            $formVersion = FormVersion::with([
                'stages.sections.fields',
                'stageTransitions'
            ])->findOrFail($formVersionId);

            $stageIds = [];
            $sectionIds = [];
            $fieldIds = [];
            foreach ($formVersion->stages as $stage) {
                $stageIds[] = $stage->id;

                foreach ($stage->sections as $section) {
                    $sectionIds[] = $section->id;

                    foreach ($section->fields as $field) {
                        $fieldIds[] = $field->id;
                    }
                }
            }

            $transitionIds = $formVersion->stageTransitions->pluck('id')->toArray();

            // Delete stage translations (NEW)
            if (!empty($stageIds)) {
                StageTranslation::whereIn('stage_id', $stageIds)
                    ->where('language_id', $languageId)
                    ->delete();
            }

            // Delete section translations (NEW)
            if (!empty($sectionIds)) {
                SectionTranslation::whereIn('section_id', $sectionIds)
                    ->where('language_id', $languageId)
                    ->delete();
            }

            // Delete transition translations (NEW)
            if (!empty($transitionIds)) {
                StageTransitionTranslation::whereIn('stage_transition_id', $transitionIds)
                    ->where('language_id', $languageId)
                    ->delete();
            }

            // Delete field translations (existing)
            if (!empty($fieldIds)) {
                FieldTranslation::whereIn('field_id', $fieldIds)
                    ->where('language_id', $languageId)
                    ->delete();
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Translations deleted successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
