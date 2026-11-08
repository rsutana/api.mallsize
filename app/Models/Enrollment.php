<?php

namespace App\Models;

use App\Models\Lookups\EnrollmentPaymentStatus;
use App\Models\Lookups\EnrollmentStatus;
use App\Models\Lookups\PaymentStatus;
use Carbon\CarbonImmutable;
use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A student's seat in one course set. Fee and minimum down payment are
 * snapshotted (centavos) at enrollment time so later price edits never
 * change what a student owes.
 *
 * @property int $id
 * @property int $student_id
 * @property int $course_id
 * @property int $batch_id
 * @property int $batch_set_id
 * @property int|null $location_id
 * @property int $fee
 * @property int $min_down_payment
 * @property string $status
 * @property CarbonImmutable|null $expires_at
 * @property CarbonImmutable|null $enrolled_at
 * @property int|string|null $amount_paid_sum
 * @property-read Student $student
 * @property-read Course $course
 * @property-read Batch $batch
 * @property-read BatchSet $batchSet
 * @property-read Location|null $location
 */
#[Fillable(['student_id', 'course_id', 'batch_id', 'batch_set_id', 'location_id', 'fee', 'min_down_payment', 'status', 'expires_at', 'enrolled_at'])]
class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fee' => 'integer',
            'min_down_payment' => 'integer',
            'expires_at' => 'datetime',
            'enrolled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return BelongsTo<Batch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * @return BelongsTo<BatchSet, $this>
     */
    public function batchSet(): BelongsTo
    {
        return $this->belongsTo(BatchSet::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return HasMany<PaymentAllocation, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeHoldingSlot(Builder $query): void
    {
        $query->where(function (Builder $q): void {
            $q->where('status', EnrollmentStatus::ENROLLED)
                ->orWhere(function (Builder $q): void {
                    $q->where('status', EnrollmentStatus::PENDING_PAYMENT)
                        ->where('expires_at', '>', now());
                });
        });
    }

    /**
     * Enrollments a student is or may become committed to (shown in the portal).
     *
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereIn('status', [EnrollmentStatus::ENROLLED, EnrollmentStatus::PENDING_PAYMENT]);
    }

    /**
     * Adds `amount_paid_sum`: total of allocations on settled payments.
     *
     * @param  Builder<self>  $query
     */
    public function scopeWithAmountPaid(Builder $query): void
    {
        $query->withSum(['allocations as amount_paid_sum' => function (Builder $q): void {
            $q->whereHas('payment', fn (Builder $p) => $p->whereIn('status', PaymentStatus::SETTLED));
        }], 'amount');
    }

    /**
     * Filters by derived payment status, matching {@see paymentStatus()}.
     *
     * @param  Builder<self>  $query
     */
    public function scopeWherePaymentStatus(Builder $query, string $status): void
    {
        $paid = '(select coalesce(sum(pa.amount), 0) from payment_allocations pa inner join payments p on p.id = pa.payment_id'
            .' where pa.enrollment_id = enrollments.id and p.status in (?, ?))';
        $bindings = [PaymentStatus::PAID, PaymentStatus::VERIFIED];

        match ($status) {
            EnrollmentPaymentStatus::FULLY_PAID => $query->whereRaw("{$paid} >= enrollments.fee", $bindings),
            EnrollmentPaymentStatus::DOWN_PAYMENT_ONLY => $query->whereRaw("{$paid} > 0", $bindings)->whereRaw("{$paid} < enrollments.fee", $bindings),
            EnrollmentPaymentStatus::AWAITING_PAYMENT => $query->whereRaw("{$paid} = 0", $bindings),
            default => $query->whereRaw('1 = 0'),
        };
    }

    public function amountPaid(): int
    {
        if (array_key_exists('amount_paid_sum', $this->attributes)) {
            return (int) $this->attributes['amount_paid_sum'];
        }

        return (int) $this->allocations()
            ->whereHas('payment', fn (Builder $p) => $p->whereIn('status', PaymentStatus::SETTLED))
            ->sum('amount');
    }

    public function balance(): int
    {
        return max(0, $this->fee - $this->amountPaid());
    }

    /**
     * Code from the enrollment_payment_statuses lookup.
     */
    public function paymentStatus(): string
    {
        $paid = $this->amountPaid();

        return match (true) {
            $paid >= $this->fee => EnrollmentPaymentStatus::FULLY_PAID,
            $paid > 0 => EnrollmentPaymentStatus::DOWN_PAYMENT_ONLY,
            default => EnrollmentPaymentStatus::AWAITING_PAYMENT,
        };
    }
}
