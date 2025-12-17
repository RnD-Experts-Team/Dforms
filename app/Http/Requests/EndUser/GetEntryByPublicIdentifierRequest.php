<?php

namespace App\Http\Requests\EndUser;

use Illuminate\Foundation\Http\FormRequest;

class GetEntryByPublicIdentifierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'public_identifier' => $this->route('public_identifier'),
        ]);
    }

    public function rules(): array
    {
        return [
            'public_identifier' => 'required|string|exists:entries,public_identifier',
            'language_id' => 'nullable|integer|exists:languages,id',
        ];
    }

    public function messages(): array
    {
        return [
            'public_identifier.required' => 'Public identifier is required.',
            'public_identifier.string' => 'Public identifier must be a string.',
            'public_identifier.exists' => 'Entry not found.',
            'language_id.integer' => 'Language ID must be an integer.',
            'language_id.exists' => 'The selected language does not exist.',
        ];
    }
}
