<?php

namespace App\Http\Resources;

use App\Models\Student;
use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Student
 */
class StudentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_no' => $this->reference_no,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'birthdate' => $this->birthdate?->toDateString(),
            'sex' => $this->sex,
            'sex_label' => Lookups::label('sexes', $this->sex),
            'education' => $this->education,
            'education_label' => Lookups::label('education_levels', $this->education),
            'address' => $this->address,
            'email' => $this->whenLoaded('user', fn () => $this->user->email),
            'mobile' => $this->whenLoaded('user', fn () => $this->user->mobile),
        ];
    }
}
