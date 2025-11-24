<?php

namespace App\Http\Requests\FieldType;

use Illuminate\Foundation\Http\FormRequest;

class StoreFieldTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Field type name is required.',
            'name.max' => 'Field type name cannot exceed 100 characters.',
        ];
    }
}
