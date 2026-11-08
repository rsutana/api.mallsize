<?php

namespace App\Models;

use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Partner meat shop where trainees do their hands-on training.
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $contact_person
 * @property string $mobile
 * @property string|null $email
 * @property string|null $street
 * @property string|null $barangay
 * @property string $city
 * @property string|null $province
 * @property string|null $zip
 * @property string|null $map_url
 * @property string|null $notes
 * @property bool $is_active
 */
#[Fillable(['name', 'type', 'contact_person', 'mobile', 'email', 'street', 'barangay', 'city', 'province', 'zip', 'map_url', 'notes', 'is_active'])]
class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
