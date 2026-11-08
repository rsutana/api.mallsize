<?php

namespace App\Http\Requests\Course;

use App\Support\Lookups;
use Illuminate\Foundation\Http\FormRequest;

class SaveCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'fee' => ['required', 'integer', 'min:100'],
            'min_down_payment' => ['required', 'integer', 'min:0', 'lte:fee'],
            'status' => ['required', Lookups::rule('course_statuses')],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'batches' => ['required', 'array', 'min:1'],
            'batches.*.id' => ['nullable', 'integer'],
            'batches.*.name' => ['required', 'string', 'max:255'],
            'batches.*.status' => ['required', Lookups::rule('batch_statuses')],
            'batches.*.sets' => ['required', 'array', 'min:1'],
            'batches.*.sets.*.id' => ['nullable', 'integer'],
            'batches.*.sets.*.name' => ['required', 'string', 'max:100'],
            'batches.*.sets.*.slots' => ['required', 'integer', 'min:1', 'max:1000'],
            'batches.*.sets.*.start_date' => ['required', 'date'],
            'batches.*.sets.*.training_days' => ['required', 'integer', 'min:1', 'max:365'],
            'batches.*.sets.*.time_label' => ['nullable', 'string', 'max:60'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'min_down_payment.lte' => 'Down payment can’t be more than the course fee.',
            'batches.required' => 'Add at least one batch.',
            'batches.*.name.required' => 'Give every batch a name.',
            'batches.*.sets.required' => 'A batch needs at least one set.',
        ];
    }
}
