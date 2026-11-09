<?php

namespace App\Http\Requests\Orientation;

use Illuminate\Foundation\Http\FormRequest;

class IndexOrientationRequest extends FormRequest
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
