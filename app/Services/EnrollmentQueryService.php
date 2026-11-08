<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Lookups\EnrollmentStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * Back-office enrollment listing filters, shared by the table and CSV export.
 */
class EnrollmentQueryService
{
    /**
     * @param  array{course_id?: int|null, batch_id?: int|null, payment_status?: string|null, q?: string|null}  $filters
     * @return Builder<Enrollment>
     */
    public function filtered(array $filters): Builder
    {
        $q = trim((string) ($filters['q'] ?? ''));

        return Enrollment::query()
            ->where('status', EnrollmentStatus::ENROLLED)
            ->withAmountPaid()
            ->with(['student.user', 'course', 'batch', 'batchSet', 'location'])
            ->when($filters['course_id'] ?? null, fn (Builder $b, $id) => $b->where('course_id', $id))
            ->when($filters['batch_id'] ?? null, fn (Builder $b, $id) => $b->where('batch_id', $id))
            ->when($filters['payment_status'] ?? null, fn (Builder $b, $status) => $b->wherePaymentStatus($status))
            ->when($q !== '', function (Builder $b) use ($q): void {
                $like = '%'.$q.'%';
                $b->whereHas('student', function (Builder $s) use ($like): void {
                    $s->where('reference_no', 'like', $like)
                        ->orWhere('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhereHas('user', fn (Builder $u) => $u->where('email', 'like', $like));
                });
            })
            ->latest('enrolled_at');
    }
}
