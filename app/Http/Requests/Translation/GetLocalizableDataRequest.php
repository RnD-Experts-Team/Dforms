<?php

namespace App\Http\Requests\Translation;

use Illuminate\Foundation\Http\FormRequest;

class GetLocalizableDataRequest extends FormRequest
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
        ];
    }
}
