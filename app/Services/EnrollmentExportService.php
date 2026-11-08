<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Support\Lookups;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnrollmentExportService
{
    private const HEADERS = ['Reference', 'Name', 'Email', 'Mobile', 'Course', 'Batch', 'Set', 'Schedule (approx.)', 'Time', 'Location', 'Fee', 'Paid', 'Balance', 'Payment status'];

    /**
     * @param  Builder<Enrollment>  $query
     */
    public function download(Builder $query): StreamedResponse
    {
        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');

            if ($out === false) {
                return;
            }

            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, self::HEADERS);

            $query->chunk(500, function ($enrollments) use ($out): void {
                foreach ($enrollments as $enrollment) {
                    fputcsv($out, $this->row($enrollment));
                }
            });

            fclose($out);
        }, 'mallsize-enrolled-students-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return list<string|int|float>
     */
    private function row(Enrollment $e): array
    {
        $set = $e->batchSet;

        return [
            $e->student->reference_no,
            $e->student->full_name,
            $e->student->user->email,
            (string) $e->student->user->mobile,
            $e->course->name,
            $e->batch->name,
            $set->name,
            $set->start_date->format('M j, Y').' – '.$set->endDate()->format('M j, Y'),
            $set->time_label,
            (string) $e->location?->name,
            $e->fee / 100,
            $e->amountPaid() / 100,
            $e->balance() / 100,
            (string) Lookups::label('enrollment_payment_statuses', $e->paymentStatus()),
        ];
    }
}
