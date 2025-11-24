<?php

namespace App\Http\Requests\Category;

use Illuminate\Foundation\Http\FormRequest;

class AssignFormsToCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:categories,id',
            'form_ids' => 'required|array|min:1',
            'form_ids.*' => 'integer|exists:forms,id',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'The selected category does not exist.',
            'form_ids.required' => 'At least one form must be selected.',
            'form_ids.array' => 'Forms must be provided as an array.',
            'form_ids.*.exists' => 'One or more selected forms do not exist.',
        ];
    }
}
