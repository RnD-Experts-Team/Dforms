<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes',
                'string',
                'max:10',
                Rule::unique('languages', 'code')->ignore($this->route('id'))
            ],
            'name' => 'sometimes|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'This language code already exists.',
        ];
    }
}
