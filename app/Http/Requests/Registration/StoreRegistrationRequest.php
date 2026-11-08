<?php

namespace App\Http\Requests\Registration;

use App\Concerns\PasswordValidationRules;
use App\Support\Lookups;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistrationRequest extends FormRequest
{
    use PasswordValidationRules;

    public function authorize(): bool
    {
        return $this->user() === null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'mobile' => ['required', 'string', 'max:20', 'regex:/^(\+?63|0)9\d{2}\s?\d{3}\s?\d{4}$/'],
            'password' => $this->passwordRules(),
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'sex' => ['nullable', Lookups::rule('sexes')],
            'education' => ['nullable', Lookups::rule('education_levels')],
            'address' => ['nullable', 'string', 'max:500'],
            'location_id' => ['required', 'integer', Rule::exists('locations', 'id')->where('is_active', true)],
            'set_ids' => ['required', 'array', 'min:1', 'max:10'],
            'set_ids.*' => ['integer', 'distinct', Rule::exists('batch_sets', 'id')],
            'is_email_notifications' => ['sometimes', 'boolean'],
            'is_sms_notifications' => ['sometimes', 'boolean'],
            'is_agreed' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.regex' => 'Enter a Philippine mobile number, e.g. 0917 555 0148.',
            'set_ids.required' => 'Select at least one course.',
            'location_id.required' => 'Choose a training location.',
            'is_agreed.accepted' => 'Please confirm your information and agree to the Terms and Data Privacy Notice.',
        ];
    }
}
