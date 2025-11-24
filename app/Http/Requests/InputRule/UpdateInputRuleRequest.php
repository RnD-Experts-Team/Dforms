<?php

namespace App\Http\Requests\InputRule;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInputRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'description' => 'sometimes|string',
            'is_public' => 'sometimes|boolean',
            'field_type_ids' => 'sometimes|array',
            'field_type_ids.*' => 'integer|exists:field_types,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Input rule name cannot exceed 100 characters.',
            'field_type_ids.array' => 'Field types must be provided as an array.',
            'field_type_ids.*.integer' => 'Each field type ID must be an integer.',
            'field_type_ids.*.exists' => 'One or more selected field types do not exist.',
        ];
    }
}
