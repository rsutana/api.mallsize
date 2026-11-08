<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lookups\BatchStatus;
use App\Models\Lookups\CourseStatus;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Courses, batches and sets from the design. Money is in centavos.
     */
    public function run(): void
    {
        $courses = [
            ['name' => 'Meat Processing – Swine', 'fee' => 22_500, 'dp' => 5_000, 'status' => CourseStatus::OPEN,
                'description' => 'Carcass breakdown, primal and retail cuts, sanitation and cold-chain handling for pork.',
                'batches' => [
                    ['Batch 13 — December 2026', BatchStatus::OPEN, [['Set A', 20, '2026-12-01', 10], ['Set B', 15, '2026-12-14', 10]]],
                    ['Batch 14 — January 2027', BatchStatus::OPEN, [['Set A', 20, '2027-01-11', 10], ['Set B', 20, '2027-01-25', 10]]],
                    ['Batch 15 — March 2027', BatchStatus::OPEN, [['Set A', 20, '2027-03-01', 10]]],
                ]],
            ['name' => 'Beef Contora / Deboning', 'fee' => 32_500, 'dp' => 8_000, 'status' => CourseStatus::OPEN,
                'description' => 'Beef quartering, deboning, trimming and portioning to market specifications.',
                'batches' => [
                    ['Batch 6 — December 2026', BatchStatus::FULL, [['Set A', 15, '2026-12-08', 15]]],
                    ['Batch 7 — February 2027', BatchStatus::OPEN, [['Set A', 15, '2027-02-01', 15], ['Set B (weekend)', 12, '2027-02-06', 15, 'Sat–Sun, 8 AM – 5 PM']]],
                ]],
            ['name' => 'Poultry Dressing & Processing', 'fee' => 18_000, 'dp' => 4_000, 'status' => CourseStatus::OPEN,
                'description' => '[Short course description — managed in Admin › Courses]',
                'batches' => [
                    ['Batch 1 — January 2027', BatchStatus::OPEN, [['Set A', 20, '2027-01-12', 7]]],
                ]],
            ['name' => 'Sausage & Processed Meats', 'fee' => 15_000, 'dp' => 3_000, 'status' => CourseStatus::CLOSED,
                'description' => '[Short course description — managed in Admin › Courses]',
                'batches' => [
                    ['Batch 1 — January 2027', BatchStatus::CLOSED, [['Set A', 20, '2027-01-19', 5]]],
                ]],
        ];

        foreach ($courses as $i => $data) {
            $course = Course::query()->updateOrCreate(['name' => $data['name']], [
                'description' => $data['description'],
                'fee' => $data['fee'] * 100,
                'min_down_payment' => $data['dp'] * 100,
                'status' => $data['status'],
                'sort_order' => $i,
            ]);

            foreach ($data['batches'] as $bi => [$name, $status, $sets]) {
                $batch = $course->batches()->updateOrCreate(['name' => $name], ['status' => $status, 'sort_order' => $bi]);

                foreach ($sets as $set) {
                    $batch->sets()->updateOrCreate(['name' => $set[0]], [
                        'slots' => $set[1],
                        'start_date' => $set[2],
                        'training_days' => $set[3],
                        'time_label' => $set[4] ?? '8 AM – 5 PM',
                    ]);
                }
            }
        }
    }
}
