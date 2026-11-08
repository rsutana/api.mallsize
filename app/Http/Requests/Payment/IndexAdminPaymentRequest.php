<?php

namespace App\Http\Requests\Payment;

use App\Support\Lookups;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexAdminPaymentRequest extends FormRequest
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
            'status' => ['nullable', 'string', Rule::in(['all', ...array_keys(Lookups::all()['payment_statuses'])])],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ];
    }
}
