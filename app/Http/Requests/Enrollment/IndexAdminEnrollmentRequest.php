<?php

namespace App\Http\Requests\Enrollment;

use App\Support\Lookups;
use Illuminate\Foundation\Http\FormRequest;

class IndexAdminEnrollmentRequest extends FormRequest
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
            'course_id' => ['nullable', 'integer'],
            'batch_id' => ['nullable', 'integer'],
            'payment_status' => ['nullable', Lookups::rule('enrollment_payment_statuses')],
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:200'],
        ];
    }
}
