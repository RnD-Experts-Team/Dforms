<?php

namespace App\Services;

use App\Models\FormVersion;
use App\Models\Entry;
use App\Models\EntryValue;
use App\Models\Language;
use App\Models\StageTransition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EndUserFormService
{
    /**
     * Get available published forms for enduser (with localization)
     */
    public function getAvailableForms(?int $languageId = null)
    {
        // Get user's default language or system default
        $language = $this->resolveLanguage($languageId);

        // Get all published form versions
        $formVersions = FormVersion::with(['form', 'formVersionTranslations'])
            ->where('status', 'published')
            ->whereHas('form', function ($query) {
                $query->where('is_archived', false);
            })
            ->get();

        $forms = [];
        foreach ($formVersions as $version) {
            // Get localized name if exists
            $translation = $version->formVersionTranslations()
                ->where('language_id', $language->id)
                ->first();

            $forms[] = [
                'form_version_id' => $version->id,
                'form_name' => $translation ? $translation->name : $version->form->name,
                'category' => $version->form->category ? $version->form->category->name : null,
            ];
        }

        return $forms;
    }

    /**
     * Get form structure for enduser (localized, initial stage only)
     */
    public function getFormStructure(int $formVersionId, ?int $languageId = null)
    {
        $language = $this->resolveLanguage($languageId);

        $formVersion = FormVersion::with([
            'form',
            'stages.sections.fields.fieldTranslations',
            'stages.stageAccessRule'
        ])->findOrFail($formVersionId);

        // Get initial stage only
        $initialStage = $formVersion->stages()->where('is_initial', true)->first();

        if (!$initialStage) {
            throw new \Exception('Initial stage not found.');
        }

        // Check access rules for initial stage
        // Implementation of access rule checking would go here
        // For now, we'll assume access is granted

        // Get localized fields
        $localizedStage = $this->localizeStage($initialStage, $language);

        // Get available transitions from initial stage
        $transitions = StageTransition::where('from_stage_id', $initialStage->id)->get();

        return [
            'form_version_id' => $formVersion->id,
            'form_name' => $this->getLocalizedFormName($formVersion, $language),
            'stage' => $localizedStage,
            'transitions' => $transitions,
        ];
    }

    /**
     * Submit initial stage
     */
    public function submitInitialStage(array $data)
    {
        DB::beginTransaction();

        try {
            $userId = Auth::id();
            $formVersionId = $data['form_version_id'];
            $stageTransitionId = $data['stage_transition_id'];
            $fieldValues = $data['field_values'];

            // Get form version and initial stage
            $formVersion = FormVersion::with('stages')->findOrFail($formVersionId);
            $initialStage = $formVersion->stages()->where('is_initial', true)->first();

            // Get transition details
            $transition = StageTransition::findOrFail($stageTransitionId);

            // Validate transition is from initial stage
            if ($transition->from_stage_id !== $initialStage->id) {
                throw new \Exception('Invalid stage transition.');
            }

            // Determine next stage or complete
            $nextStageId = $transition->to_complete ? null : $transition->to_stage_id;
            $isComplete = $transition->to_complete;

            // Create entry
            $entry = Entry::create([
                'form_version_id' => $formVersionId,
                'current_stage_id' => $nextStageId ?? $initialStage->id,
                'is_complete' => $isComplete,
                'is_considered' => false,
                'created_by_user_id' => $userId,
            ]);

            // Save field values
            foreach ($fieldValues as $fieldValue) {
                EntryValue::create([
                    'entry_id' => $entry->id,
                    'field_id' => $fieldValue['field_id'],
                    'value' => $fieldValue['value'],
                ]);
            }

            // Execute actions attached to this transition
            // Implementation of action execution would go here
            // For now, we'll skip this

            DB::commit();

            return [
                'entry' => $entry,
                'public_identifier' => $entry->public_identifier,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get entry by public identifier for later stage filling
     */
    public function getEntryByPublicIdentifier(string $publicIdentifier, ?int $languageId = null)
    {
        $language = $this->resolveLanguage($languageId);

        $entry = Entry::with([
            'formVersion.stages.sections.fields.fieldTranslations',
            'currentStage',
            'entryValues.field'
        ])->where('public_identifier', $publicIdentifier)->firstOrFail();

        // Check access rules for current stage
        // Implementation would go here

        // Get all previous stages (read-only) and current stage (editable)
        $stages = [];
        foreach ($entry->formVersion->stages as $stage) {
            $localizedStage = $this->localizeStage($stage, $language);
            
            // Attach values for each field
            foreach ($localizedStage['sections'] as &$section) {
                foreach ($section['fields'] as &$field) {
                    $entryValue = $entry->entryValues()
                        ->where('field_id', $field['id'])
                        ->first();
                    
                    $field['value'] = $entryValue ? $entryValue->value : null;
                    $field['is_editable'] = ($stage->id === $entry->current_stage_id);
                }
            }

            $stages[] = $localizedStage;
        }

        // Get available transitions from current stage
        $transitions = StageTransition::where('from_stage_id', $entry->current_stage_id)->get();

        return [
            'entry' => $entry,
            'form_name' => $this->getLocalizedFormName($entry->formVersion, $language),
            'stages' => $stages,
            'current_stage_id' => $entry->current_stage_id,
            'transitions' => $transitions,
        ];
    }

    /**
     * Submit later stage
     */
    public function submitLaterStage(array $data)
    {
        DB::beginTransaction();

        try {
            $publicIdentifier = $data['public_identifier'];
            $stageTransitionId = $data['stage_transition_id'];
            $fieldValues = $data['field_values'];

            // Get entry
            $entry = Entry::where('public_identifier', $publicIdentifier)->firstOrFail();

            // Get transition details
            $transition = StageTransition::findOrFail($stageTransitionId);

            // Validate transition is from current stage
            if ($transition->from_stage_id !== $entry->current_stage_id) {
                throw new \Exception('Invalid stage transition.');
            }

            // Determine next stage or complete
            $nextStageId = $transition->to_complete ? null : $transition->to_stage_id;
            $isComplete = $transition->to_complete;

            // Update or create field values for current stage
            foreach ($fieldValues as $fieldValue) {
                EntryValue::updateOrCreate(
                    [
                        'entry_id' => $entry->id,
                        'field_id' => $fieldValue['field_id'],
                    ],
                    [
                        'value' => $fieldValue['value'],
                    ]
                );
            }

            // Update entry stage
            $entry->update([
                'current_stage_id' => $nextStageId ?? $entry->current_stage_id,
                'is_complete' => $isComplete,
            ]);

            // Execute actions attached to this transition
            // Implementation would go here

            DB::commit();

            return [
                'entry' => $entry,
                'public_identifier' => $entry->public_identifier,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Resolve language (user's default or system default)
     */
    private function resolveLanguage(?int $languageId)
    {
        if ($languageId) {
            return Language::findOrFail($languageId);
        }

        $user = Auth::user();
        if ($user && $user->default_language_id) {
            return Language::findOrFail($user->default_language_id);
        }

        return Language::where('is_default', true)->firstOrFail();
    }

    /**
     * Get localized form name
     */
    private function getLocalizedFormName($formVersion, $language)
    {
        $translation = $formVersion->formVersionTranslations()
            ->where('language_id', $language->id)
            ->first();

        return $translation ? $translation->name : $formVersion->form->name;
    }

    /**
     * Localize stage structure
     */
    private function localizeStage($stage, $language)
    {
        $sections = [];

        foreach ($stage->sections as $section) {
            $fields = [];

            foreach ($section->fields as $field) {
                $translation = $field->fieldTranslations()
                    ->where('language_id', $language->id)
                    ->first();

                $fields[] = [
                    'id' => $field->id,
                    'field_type_id' => $field->field_type_id,
                    'label' => $translation ? $translation->label : $field->label,
                    'placeholder' => $field->placeholder,
                    'helper_text' => $translation ? $translation->helper_text : $field->helper_text,
                    'default_value' => $translation ? $translation->default_value : $field->default_value,
                    'visibility_condition' => $field->visibility_condition,
                ];
            }

            $sections[] = [
                'id' => $section->id,
                'name' => $section->name,
                'visibility_condition' => $section->visibility_condition,
                'fields' => $fields,
            ];
        }

        return [
            'id' => $stage->id,
            'name' => $stage->name,
            'is_initial' => $stage->is_initial,
            'sections' => $sections,
        ];
    }
}
