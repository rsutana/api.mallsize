<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Lookups\EnrollmentStatus;
use App\Models\Student;
use Illuminate\Support\Collection;

class StudentDashboardService
{
    /**
     * @return Collection<int, Enrollment>
     */
    public function enrollments(Student $student): Collection
    {
        return $student->enrollments()
            ->active()
            ->withAmountPaid()
            ->with(['course', 'batch', 'location', 'batchSet', 'batch.sets' => fn ($q) => $q->withSlotsTaken()])
            ->get()
            ->sortBy(fn (Enrollment $e) => $e->batchSet->start_date)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    public function summary(Student $student): array
    {
        $enrollments = $this->enrollments($student)->where('status', EnrollmentStatus::ENROLLED)->values();
        $fee = $enrollments->sum('fee');
        $paid = $enrollments->sum(fn (Enrollment $e): int => $e->amountPaid());
        $withBalance = $enrollments->filter(fn (Enrollment $e): bool => $e->balance() > 0);
        $next = $enrollments->first(fn (Enrollment $e): bool => $e->batchSet->endDate()->isFuture() || $e->batchSet->endDate()->isToday());

        return [
            'total_fee' => $fee,
            'total_paid' => $paid,
            'total_balance' => max(0, $fee - $paid),
            'courses_count' => $enrollments->count(),
            'fully_paid_count' => $enrollments->count() - $withBalance->count(),
            'with_balance_count' => $withBalance->count(),
            'next_training' => $next ? [
                'course_name' => $next->course->name,
                'start_date' => $next->batchSet->start_date->toDateString(),
            ] : null,
            'enrollments' => $enrollments,
        ];
    }
}
