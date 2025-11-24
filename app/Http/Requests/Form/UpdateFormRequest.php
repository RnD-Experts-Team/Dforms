<?php

namespace App\Http\Requests\Form;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Form name is required.',
            'name.string' => 'Form name must be a string.',
            'name.max' => 'Form name cannot exceed 255 characters.',
            'category_id.integer' => 'Category ID must be an integer.',
            'category_id.exists' => 'The selected category does not exist.',
        ];
    }
}
