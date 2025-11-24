<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:10|unique:languages,code',
            'name' => 'required|string|max:100',
            'is_default' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Language code is required.',
            'code.unique' => 'This language code already exists.',
            'name.required' => 'Language name is required.',
        ];
    }
}
