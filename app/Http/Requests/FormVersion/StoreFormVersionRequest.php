<?php

namespace App\Http\Requests\FormVersion;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'form_id' => 'required|integer|exists:forms,id',
            'copy_from_current' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'form_id.required' => 'Form ID is required.',
            'form_id.integer' => 'Form ID must be an integer.',
            'form_id.exists' => 'The selected form does not exist.',
            'copy_from_current.required' => 'Copy from current flag is required.',
            'copy_from_current.boolean' => 'Copy from current must be a boolean.',
        ];
    }
}
