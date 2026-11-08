<?php

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Result of starting a payment: the payment plus what the browser must do
 * next (redirect to the wallet / 3-D Secure page, or show a QR code).
 *
 * @property array{payment: Payment, next_action: array<string, mixed>|null} $resource
 */
class CheckoutResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'payment' => new PaymentResource($this->resource['payment']->load('allocations.enrollment.course')),
            'next_action' => $this->resource['next_action'],
        ];
    }
}
