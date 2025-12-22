<?php

namespace App\Http\Requests\Translation;

use Illuminate\Foundation\Http\FormRequest;

class SaveTranslationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_version_id' => 'required|integer|exists:form_versions,id',
            'language_id' => 'required|integer|exists:languages,id',
            'form_name' => 'nullable|string|max:255',

            // NEW (optional): stage name translations
            'stage_translations' => 'sometimes|array',
            'stage_translations.*.stage_id' => 'required|integer|exists:stages,id',
            'stage_translations.*.name' => 'nullable|string|max:255',

            // NEW (optional): section name translations
            'section_translations' => 'sometimes|array',
            'section_translations.*.section_id' => 'required|integer|exists:sections,id',
            'section_translations.*.name' => 'nullable|string|max:255',

            // NEW (optional): transition label translations
            'transition_translations' => 'sometimes|array',
            'transition_translations.*.stage_transition_id' => 'required|integer|exists:stage_transitions,id',
            'transition_translations.*.label' => 'nullable|string|max:255',

            // existing (required)
            'field_translations' => 'required|array',
            'field_translations.*.field_id' => 'required|integer|exists:fields,id',
            'field_translations.*.label' => 'nullable|string|max:255',
            'field_translations.*.helper_text' => 'nullable|string',
            'field_translations.*.default_value' => 'nullable|string',
            'field_translations.*.place_holder' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'form_version_id.required' => 'Form version ID is required.',
            'form_version_id.integer' => 'Form version ID must be an integer.',
            'form_version_id.exists' => 'The selected form version does not exist.',
            'language_id.required' => 'Language ID is required.',
            'language_id.integer' => 'Language ID must be an integer.',
            'language_id.exists' => 'The selected language does not exist.',
            'form_name.string' => 'Form name must be a string.',
            'form_name.max' => 'Form name cannot exceed 255 characters.',

            // stage translations messages
            'stage_translations.array' => 'Stage translations must be an array.',
            'stage_translations.*.stage_id.required' => 'Stage ID is required for each stage translation.',
            'stage_translations.*.stage_id.integer' => 'Stage ID must be an integer.',
            'stage_translations.*.stage_id.exists' => 'One or more stages do not exist.',
            'stage_translations.*.name.string' => 'Stage name must be a string.',
            'stage_translations.*.name.max' => 'Stage name cannot exceed 255 characters.',

            // section translations messages
            'section_translations.array' => 'Section translations must be an array.',
            'section_translations.*.section_id.required' => 'Section ID is required for each section translation.',
            'section_translations.*.section_id.integer' => 'Section ID must be an integer.',
            'section_translations.*.section_id.exists' => 'One or more sections do not exist.',
            'section_translations.*.name.string' => 'Section name must be a string.',
            'section_translations.*.name.max' => 'Section name cannot exceed 255 characters.',

            // transition translations messages
            'transition_translations.array' => 'Transition translations must be an array.',
            'transition_translations.*.stage_transition_id.required' => 'Stage transition ID is required for each transition translation.',
            'transition_translations.*.stage_transition_id.integer' => 'Stage transition ID must be an integer.',
            'transition_translations.*.stage_transition_id.exists' => 'One or more stage transitions do not exist.',
            'transition_translations.*.label.string' => 'Transition label must be a string.',
            'transition_translations.*.label.max' => 'Transition label cannot exceed 255 characters.',

            // existing field translations messages
            'field_translations.required' => 'Field translations are required.',
            'field_translations.array' => 'Field translations must be an array.',
            'field_translations.*.field_id.required' => 'Field ID is required for each translation.',
            'field_translations.*.field_id.integer' => 'Field ID must be an integer.',
            'field_translations.*.field_id.exists' => 'One or more fields do not exist.',
            'field_translations.*.label.string' => 'Field label must be a string.',
            'field_translations.*.label.max' => 'Field label cannot exceed 255 characters.',
            'field_translations.*.helper_text.string' => 'Helper text must be a string.',
            'field_translations.*.default_value.string' => 'Default value must be a string.',
        ];
    }
}
