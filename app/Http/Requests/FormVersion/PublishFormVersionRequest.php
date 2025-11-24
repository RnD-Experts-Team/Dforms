<?php

namespace App\Http\Requests\FormVersion;

use Illuminate\Foundation\Http\FormRequest;

class PublishFormVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [];
    }
}
