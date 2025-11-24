<?php

namespace App\Http\Requests\FieldType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFieldTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Field type name cannot exceed 100 characters.',
        ];
    }
}
