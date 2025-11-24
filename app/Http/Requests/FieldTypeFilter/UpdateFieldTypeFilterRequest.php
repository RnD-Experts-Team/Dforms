<?php

namespace App\Http\Requests\FieldTypeFilter;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFieldTypeFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'field_type_id' => 'sometimes|integer|exists:field_types,id',
            'filter_method_description' => 'sometimes|string',
        ];
    }

    public function messages(): array
    {
        return [
            'field_type_id.exists' => 'The selected field type does not exist.',
        ];
    }
}
