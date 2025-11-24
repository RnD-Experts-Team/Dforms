<?php

namespace App\Http\Requests\Entry;

use Illuminate\Foundation\Http\FormRequest;

class ToggleConsideredRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_ids' => 'required|array',
            'entry_ids.*' => 'integer|exists:entries,id',
            'is_considered' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'entry_ids.required' => 'Entry IDs are required.',
            'entry_ids.array' => 'Entry IDs must be an array.',
            'entry_ids.*.integer' => 'Each entry ID must be an integer.',
            'entry_ids.*.exists' => 'One or more entries do not exist.',
            'is_considered.required' => 'Considered status is required.',
            'is_considered.boolean' => 'Considered status must be a boolean.',
        ];
    }
}
