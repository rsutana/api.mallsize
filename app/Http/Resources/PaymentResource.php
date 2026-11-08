<?php

namespace App\Http\Resources;

use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payment
 */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'amount' => $this->amount,
            'method' => $this->method,
            'method_label' => Lookups::label('payment_methods', $this->method),
            'status' => $this->status,
            'status_label' => Lookups::label('payment_statuses', $this->status),
            'provider' => $this->provider,
            'provider_reference' => $this->provider_payment_id ?? $this->provider_intent_id,
            'failure_reason' => $this->failure_reason,
            'rejected_reason' => $this->rejected_reason,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'verified_at' => $this->verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'allocations' => $this->whenLoaded('allocations', fn () => $this->allocations->map(fn (PaymentAllocation $a): array => [
                'enrollment_id' => $a->enrollment_id,
                'amount' => $a->amount,
                'amount_type' => $a->amount_type,
                'amount_type_label' => Lookups::label('payment_amount_types', $a->amount_type),
                'course_name' => $a->relationLoaded('enrollment') && $a->enrollment->relationLoaded('course') ? $a->enrollment->course->name : null,
            ])->values()),
            'student' => new StudentResource($this->whenLoaded('student')),
        ];
    }
}
