<?php

namespace App\Http\Requests\Payment;

use App\Models\Lookups\PaymentMethod;
use App\Services\Payments\PaymentGateway;
use App\Support\Lookups;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
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
            'method' => ['required', Rule::in(PaymentMethod::ONLINE), Lookups::rule('payment_methods')],
            'payment_method_id' => ['nullable', 'string', 'max:100', Rule::requiredIf($this->input('method') === PaymentMethod::CARD && $this->gatewayNeedsCardToken())],
            'items' => ['required', 'array', 'min:1', 'max:10'],
            'items.*.enrollment_id' => ['required', 'integer', 'distinct'],
            'items.*.amount_type' => ['required', Rule::in(['down_payment', 'full', 'custom'])],
            'items.*.amount' => ['nullable', 'integer', 'min:1', 'required_if:items.*.amount_type,custom'],
        ];
    }

    private function gatewayNeedsCardToken(): bool
    {
        return app(PaymentGateway::class)->publicKey() !== null;
    }
}
