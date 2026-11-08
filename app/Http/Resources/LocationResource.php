<?php

namespace App\Http\Resources;

use App\Models\Location;
use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Location
 */
class LocationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'type_label' => Lookups::label('location_types', $this->type),
            'contact_person' => $this->contact_person,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'street' => $this->street,
            'barangay' => $this->barangay,
            'city' => $this->city,
            'province' => $this->province,
            'zip' => $this->zip,
            'map_url' => $this->map_url,
            'notes' => $this->when((bool) $request->user()?->isStaff(), $this->notes),
            'is_active' => $this->is_active,
        ];
    }
}
