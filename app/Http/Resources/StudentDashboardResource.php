<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array<string, mixed> $resource
 */
class StudentDashboardResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_fee' => $this->resource['total_fee'],
            'total_paid' => $this->resource['total_paid'],
            'total_balance' => $this->resource['total_balance'],
            'courses_count' => $this->resource['courses_count'],
            'fully_paid_count' => $this->resource['fully_paid_count'],
            'with_balance_count' => $this->resource['with_balance_count'],
            'next_training' => $this->resource['next_training'],
            'enrollments' => EnrollmentResource::collection($this->resource['enrollments']),
        ];
    }
}
