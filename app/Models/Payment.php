<?php

namespace App\Models;

use App\Models\Lookups\PaymentStatus;
use Carbon\CarbonImmutable;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One checkout. Its amount (centavos) is split across enrollments through
 * payment allocations.
 *
 * @property int $id
 * @property int $student_id
 * @property string $reference_no
 * @property int $amount
 * @property string $method
 * @property string $status
 * @property string $provider
 * @property string|null $provider_intent_id
 * @property string|null $provider_payment_id
 * @property string|null $provider_reference
 * @property string|null $failure_reason
 * @property CarbonImmutable|null $paid_at
 * @property CarbonImmutable|null $verified_at
 * @property int|null $verified_by
 * @property string|null $rejected_reason
 * @property CarbonImmutable|null $created_at
 * @property-read Student $student
 * @property-read User|null $verifier
 */
#[Fillable(['student_id', 'reference_no', 'amount', 'method', 'status', 'provider', 'provider_intent_id', 'provider_payment_id', 'provider_reference', 'failure_reason', 'paid_at', 'verified_at', 'verified_by', 'rejected_reason'])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
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
     * @return HasMany<PaymentAllocation, $this>
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isSettled(): bool
    {
        return in_array($this->status, PaymentStatus::SETTLED, true);
    }
}
