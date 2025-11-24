<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SetDefaultLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language_id' => 'required|integer|exists:languages,id',
        ];
    }

    public function messages(): array
    {
        return [
            'language_id.required' => 'Language ID is required.',
            'language_id.integer' => 'Language ID must be an integer.',
            'language_id.exists' => 'The selected language does not exist.',
        ];
    }
}
