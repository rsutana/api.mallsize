<?php

namespace App\Http\Requests\NewsPost;

use Illuminate\Foundation\Http\FormRequest;

class IndexNewsPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isStaff();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }
}
