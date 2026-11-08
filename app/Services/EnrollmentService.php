<?php

namespace App\Services;

use App\Models\BatchSet;
use App\Models\Enrollment;
use App\Models\Location;
use App\Models\Lookups\BatchStatus;
use App\Models\Lookups\CourseStatus;
use App\Models\Lookups\EnrollmentStatus;
use App\Models\Lookups\PaymentStatus;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    /**
     * Reserve slots in the given sets for a student. Each reservation is a
     * pending enrollment that expires unless its down payment is paid.
     *
     * @param  list<int>  $setIds
     * @return Collection<int, Enrollment>
     *
     * @throws ValidationException
     */
    public function createPending(Student $student, Location $location, array $setIds): Collection
    {
        return DB::transaction(function () use ($student, $location, $setIds): Collection {
            $sets = BatchSet::query()
                ->whereIn('id', $setIds)
                ->with('batch.course')
                ->lockForUpdate()
                ->get();

            if ($sets->count() !== count(array_unique($setIds))) {
                throw ValidationException::withMessages(['set_ids' => 'One of the selected schedules no longer exists.']);
            }

            if ($sets->pluck('batch.course_id')->duplicates()->isNotEmpty()) {
                throw ValidationException::withMessages(['set_ids' => 'Choose only one schedule per course.']);
            }

            $alreadyIn = $student->enrollments()
                ->active()
                ->whereIn('course_id', $sets->pluck('batch.course_id'))
                ->where(fn (Builder $q) => $q->where('status', EnrollmentStatus::ENROLLED)->orWhere('expires_at', '>', now()))
                ->with('course')
                ->get();

            if ($alreadyIn->isNotEmpty()) {
                throw ValidationException::withMessages(['set_ids' => 'You are already enrolled in '.$alreadyIn->pluck('course.name')->implode(', ').'.']);
            }

            foreach ($sets as $set) {
                $course = $set->batch->course;

                if ($course->status !== CourseStatus::OPEN || $set->batch->status !== BatchStatus::OPEN) {
                    throw ValidationException::withMessages(['set_ids' => $course->name.' — '.$set->batch->name.' is not open for enrollment.']);
                }

                if ($set->slotsLeft() < 1) {
                    throw ValidationException::withMessages(['set_ids' => $course->name.' — '.$set->name.' is fully booked. Please choose another set.']);
                }
            }

            $expiresAt = now()->addMinutes((int) config('mallsize.pending_enrollment_ttl_minutes'));

            return $sets->map(fn (BatchSet $set): Enrollment => $student->enrollments()->create([
                'course_id' => $set->batch->course_id,
                'batch_id' => $set->batch_id,
                'batch_set_id' => $set->id,
                'location_id' => $location->id,
                'fee' => $set->batch->course->fee,
                'min_down_payment' => $set->batch->course->min_down_payment,
                'status' => EnrollmentStatus::PENDING_PAYMENT,
                'expires_at' => $expiresAt,
            ]))->values();
        });
    }

    public function activate(Enrollment $enrollment): void
    {
        if ($enrollment->status === EnrollmentStatus::ENROLLED) {
            return;
        }

        $enrollment->forceFill([
            'status' => EnrollmentStatus::ENROLLED,
            'enrolled_at' => now(),
            'expires_at' => null,
        ])->save();
    }

    /**
     * Keep a reservation alive while its payment is in progress.
     */
    public function extendHold(Enrollment $enrollment): void
    {
        if ($enrollment->status === EnrollmentStatus::PENDING_PAYMENT) {
            $enrollment->forceFill([
                'expires_at' => now()->addMinutes((int) config('mallsize.pending_enrollment_ttl_minutes')),
            ])->save();
        }
    }

    /**
     * Release reservations that were never paid. Reservations with a
     * payment still in progress are kept.
     */
    public function expireStale(): int
    {
        return Enrollment::query()
            ->where('status', EnrollmentStatus::PENDING_PAYMENT)
            ->where('expires_at', '<=', now())
            ->whereDoesntHave('allocations.payment', fn (Builder $q) => $q->whereIn('status', [PaymentStatus::PENDING, ...PaymentStatus::SETTLED]))
            ->update(['status' => EnrollmentStatus::EXPIRED, 'updated_at' => now()]);
    }
}
