<?php

namespace App\Http\Requests\Student;

use App\Support\Lookups;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isStudent();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()?->id)],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^(\+?63|0)9\d{2}\s?\d{3}\s?\d{4}$/'],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'sex' => ['nullable', Lookups::rule('sexes')],
            'education' => ['nullable', Lookups::rule('education_levels')],
            'address' => ['nullable', 'string', 'max:500'],
            'is_email_notifications' => ['required', 'boolean'],
            'is_sms_notifications' => ['required', 'boolean'],
        ];
    }
}
