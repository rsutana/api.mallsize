<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Student;

/**
 * Issues human-readable reference numbers: MS-2026-0148 for students and
 * MS-RC-10482 for payment receipts. Unique indexes back these up.
 */
class ReferenceNumberService
{
    public function nextStudentReference(): string
    {
        $prefix = config('mallsize.reference_prefixes.student').'-'.now()->year.'-';

        $last = Student::query()
            ->where('reference_no', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('reference_no')
            ->value('reference_no');

        $next = $last ? ((int) substr((string) $last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function nextReceiptReference(): string
    {
        $prefix = config('mallsize.reference_prefixes.receipt').'-';

        $last = Payment::query()
            ->where('reference_no', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByRaw('length(reference_no) desc')
            ->orderByDesc('reference_no')
            ->value('reference_no');

        $next = $last ? ((int) substr((string) $last, strlen($prefix))) + 1 : 10001;

        return $prefix.$next;
    }
}
