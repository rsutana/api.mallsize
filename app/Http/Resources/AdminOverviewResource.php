<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array<string, mixed> $resource
 */
class AdminOverviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'payments_to_verify' => $this->resource['payments_to_verify'],
            'enrolled_count' => $this->resource['enrolled_count'],
            'collected' => $this->resource['collected'],
            'outstanding' => $this->resource['outstanding'],
            'due_soon' => EnrollmentResource::collection($this->resource['due_soon']),
            'notifications' => NotificationLogResource::collection($this->resource['notifications']),
        ];
    }
}
