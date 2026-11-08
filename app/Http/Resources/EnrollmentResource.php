<?php

namespace App\Http\Resources;

use App\Models\Enrollment;
use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Money fields are centavos; amount_paid counts paid and verified payments.
 *
 * @mixin Enrollment
 */
class EnrollmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $paymentStatus = $this->paymentStatus();

        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => Lookups::label('enrollment_statuses', $this->status),
            'fee' => $this->fee,
            'min_down_payment' => $this->min_down_payment,
            'amount_paid' => $this->amountPaid(),
            'balance' => $this->balance(),
            'payment_status' => $paymentStatus,
            'payment_status_label' => Lookups::label('enrollment_payment_statuses', $paymentStatus),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'enrolled_at' => $this->enrolled_at?->toIso8601String(),
            'course' => $this->whenLoaded('course', fn () => [
                'id' => $this->course->id,
                'name' => $this->course->name,
                'slug' => $this->course->slug,
            ]),
            'batch' => new BatchResource($this->whenLoaded('batch')),
            'set' => new BatchSetResource($this->whenLoaded('batchSet')),
            'location' => new LocationResource($this->whenLoaded('location')),
            'student' => new StudentResource($this->whenLoaded('student')),
        ];
    }
}
