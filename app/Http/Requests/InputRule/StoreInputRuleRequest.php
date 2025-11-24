<?php

namespace App\Http\Requests\InputRule;

use Illuminate\Foundation\Http\FormRequest;

class StoreInputRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'required|string',
            'is_public' => 'sometimes|boolean',
            'field_type_ids' => 'required|array',
            'field_type_ids.*' => 'integer|exists:field_types,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Input rule name is required.',
            'name.max' => 'Input rule name cannot exceed 100 characters.',
            'description.required' => 'Input rule description is required.',
            'field_type_ids.required' => 'At least one field type must be selected.',
            'field_type_ids.array' => 'Field types must be provided as an array.',
            'field_type_ids.*.integer' => 'Each field type ID must be an integer.',
            'field_type_ids.*.exists' => 'One or more selected field types do not exist.',
        ];
    }
}
