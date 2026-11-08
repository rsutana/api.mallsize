<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Lookups\EnrollmentPaymentStatus;
use App\Models\Lookups\EnrollmentStatus;
use App\Notifications\BalanceReminderNotification;

class BalanceReminderService
{
    public function __construct(private readonly NotificationLogService $log) {}

    /**
     * Remind every enrolled student who still owes a balance.
     *
     * @return int Number of students reminded
     */
    public function sendAll(): int
    {
        $byStudent = Enrollment::query()
            ->where('status', EnrollmentStatus::ENROLLED)
            ->wherePaymentStatus(EnrollmentPaymentStatus::DOWN_PAYMENT_ONLY)
            ->withAmountPaid()
            ->with(['course', 'batchSet', 'student.user'])
            ->get()
            ->groupBy('student_id');

        foreach ($byStudent as $enrollments) {
            $enrollments->first()->student->user->notify(new BalanceReminderNotification($enrollments->values()));
        }

        $this->log->record('Balance reminders sent to '.$byStudent->count().' students', $byStudent->count());

        return $byStudent->count();
    }
}
