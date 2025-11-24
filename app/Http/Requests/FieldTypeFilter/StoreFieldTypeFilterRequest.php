<?php

namespace App\Http\Requests\FieldTypeFilter;

use Illuminate\Foundation\Http\FormRequest;

class StoreFieldTypeFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'field_type_id' => 'required|integer|exists:field_types,id',
            'filter_method_description' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'field_type_id.required' => 'Field type is required.',
            'field_type_id.exists' => 'The selected field type does not exist.',
            'filter_method_description.required' => 'Filter method description is required.',
        ];
    }
}
