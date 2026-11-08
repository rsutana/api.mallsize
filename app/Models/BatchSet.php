<?php

namespace App\Models;

use App\Models\Lookups\EnrollmentStatus;
use Database\Factories\BatchSetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * A schedule option inside a batch, with its own slots and training dates.
 *
 * @property int $id
 * @property int $batch_id
 * @property string $name
 * @property int $slots
 * @property CarbonImmutable $start_date
 * @property int $training_days
 * @property string $time_label
 * @property int|null $slots_taken_count
 * @property-read Batch $batch
 */
#[Fillable(['batch_id', 'name', 'slots', 'start_date', 'training_days', 'time_label'])]
class BatchSet extends Model
{
    /** @use HasFactory<BatchSetFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'slots' => 'integer',
            'start_date' => 'date',
            'training_days' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Batch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Enrollments that hold a slot: enrolled ones plus unexpired pending ones.
     *
     * @return HasMany<Enrollment, $this>
     */
    public function holdingEnrollments(): HasMany
    {
        return $this->enrollments()->holdingSlot();
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeWithSlotsTaken(Builder $query): void
    {
        $query->withCount(['holdingEnrollments as slots_taken_count']);
    }

    public function slotsTaken(): int
    {
        return $this->slots_taken_count ?? $this->holdingEnrollments()->count();
    }

    public function slotsLeft(): int
    {
        return max(0, $this->slots - $this->slotsTaken());
    }

    public function endDate(): CarbonInterface
    {
        return $this->start_date->copy()->addDays(max(1, $this->training_days) - 1);
    }

    /**
     * Whether this set has enrollments that must not be orphaned.
     */
    public function hasEnrollments(): bool
    {
        return $this->enrollments()
            ->whereIn('status', [EnrollmentStatus::ENROLLED, EnrollmentStatus::PENDING_PAYMENT])
            ->exists();
    }
}
