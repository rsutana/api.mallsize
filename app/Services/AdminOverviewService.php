<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lookups\CourseStatus;
use App\Models\Lookups\EnrollmentPaymentStatus;
use App\Models\Lookups\EnrollmentStatus;
use App\Models\Lookups\PaymentStatus;
use App\Models\NotificationLog;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminOverviewService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $enrolled = Enrollment::query()->where('status', EnrollmentStatus::ENROLLED)->withAmountPaid()->get();

        return [
            'payments_to_verify' => Payment::query()->where('status', PaymentStatus::PAID)->count(),
            'enrolled_count' => $enrolled->count(),
            'collected' => (int) Payment::query()->whereIn('status', PaymentStatus::SETTLED)->sum('amount'),
            'outstanding' => $enrolled->sum(fn (Enrollment $e): int => $e->balance()),
            'due_soon' => Enrollment::query()
                ->where('status', EnrollmentStatus::ENROLLED)
                ->wherePaymentStatus(EnrollmentPaymentStatus::DOWN_PAYMENT_ONLY)
                ->join('batch_sets', 'batch_sets.id', '=', 'enrollments.batch_set_id')
                ->whereDate('batch_sets.start_date', '>=', today())
                ->orderBy('batch_sets.start_date')
                ->select('enrollments.*')
                ->withAmountPaid()
                ->with(['student.user', 'course', 'batch', 'batchSet'])
                ->limit(10)
                ->get(),
            'notifications' => NotificationLog::query()->latest('sent_at')->limit(10)->get(),
            'courses' => $this->capacity(),
        ];
    }

    /**
     * Non-archived courses with every batch and set, counting enrolled students
     * and slots held (enrolled + unexpired pending) per set.
     *
     * @return Collection<int, Course>
     */
    public function capacity(): Collection
    {
        return Course::query()
            ->where('status', '!=', CourseStatus::ARCHIVED)
            ->ordered()
            ->with(['batches.sets' => function (HasMany $sets): void {
                $sets->withSlotsTaken()->withCount([
                    'enrollments as enrolled_count' => fn (Builder $e) => $e->where('status', EnrollmentStatus::ENROLLED),
                ]);
            }])
            ->get();
    }
}
