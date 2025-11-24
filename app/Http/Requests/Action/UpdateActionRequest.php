<?php

namespace App\Http\Requests\Action;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'props_description' => 'sometimes|string',
            'is_public' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Action name cannot exceed 100 characters.',
        ];
    }
}
