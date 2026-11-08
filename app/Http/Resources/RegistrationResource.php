<?php

namespace App\Http\Resources;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @property array{user: User, enrollments: Collection<int, Enrollment>} $resource
 */
class RegistrationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this->resource['user']->load('student')),
            'enrollments' => EnrollmentResource::collection(
                $this->resource['enrollments']->each->load(['course', 'batch', 'batchSet', 'location']),
            ),
        ];
    }
}
