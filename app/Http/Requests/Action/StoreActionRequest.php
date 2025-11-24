<?php

namespace App\Http\Requests\Action;

use Illuminate\Foundation\Http\FormRequest;

class StoreActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'props_description' => 'required|string',
            'is_public' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Action name is required.',
            'name.max' => 'Action name cannot exceed 100 characters.',
            'props_description.required' => 'Props description is required.',
        ];
    }
}
