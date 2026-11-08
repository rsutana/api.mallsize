<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\BatchSet;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Saves a course together with its nested batches and sets.
 */
class CourseService
{
    /**
     * @param  array<string, mixed>  $data  Validated course payload with nested batches/sets
     *
     * @throws ValidationException
     */
    public function save(array $data, ?Course $course = null): Course
    {
        return DB::transaction(function () use ($data, $course): Course {
            $course ??= new Course;
            $course->fill([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'fee' => $data['fee'],
                'min_down_payment' => $data['min_down_payment'],
                'status' => $data['status'],
                'sort_order' => $data['sort_order'] ?? $course->sort_order ?? 0,
            ])->save();

            $keptBatchIds = [];

            foreach (array_values($data['batches']) as $bi => $batchData) {
                /** @var Batch $batch */
                $batch = isset($batchData['id'])
                    ? $course->batches()->findOrFail($batchData['id'])
                    : new Batch(['course_id' => $course->id]);

                $batch->fill([
                    'course_id' => $course->id,
                    'name' => $batchData['name'],
                    'status' => $batchData['status'],
                    'sort_order' => $bi,
                ])->save();

                $keptBatchIds[] = $batch->id;
                $keptSetIds = [];

                foreach ($batchData['sets'] as $setData) {
                    /** @var BatchSet $set */
                    $set = isset($setData['id'])
                        ? $batch->sets()->findOrFail($setData['id'])
                        : new BatchSet(['batch_id' => $batch->id]);

                    $set->fill([
                        'batch_id' => $batch->id,
                        'name' => $setData['name'],
                        'slots' => $setData['slots'],
                        'start_date' => $setData['start_date'],
                        'training_days' => $setData['training_days'],
                        'time_label' => $setData['time_label'] ?? '8 AM – 5 PM',
                    ])->save();

                    $keptSetIds[] = $set->id;
                }

                foreach ($batch->sets()->whereNotIn('id', $keptSetIds)->get() as $removed) {
                    $this->guardRemoval($removed->enrollments()->exists(), $removed->name.' in '.$batch->name.' has enrolled students and can’t be removed.');
                    $removed->delete();
                }
            }

            foreach ($course->batches()->whereNotIn('id', $keptBatchIds)->get() as $removed) {
                $this->guardRemoval($removed->enrollments()->exists(), $removed->name.' has enrolled students — set it to Closed instead of removing it.');
                $removed->delete();
            }

            return $course->refresh();
        });
    }

    /**
     * @throws ValidationException
     */
    public function delete(Course $course): void
    {
        $count = $course->enrollments()->count();

        $this->guardRemoval($count > 0, 'Can’t delete “'.$course->name.'” — '.$count.' student(s) enrolled. Set its status to Archived instead.');

        $course->delete();
    }

    /**
     * @throws ValidationException
     */
    private function guardRemoval(bool $blocked, string $message): void
    {
        if ($blocked) {
            throw ValidationException::withMessages(['course' => $message]);
        }
    }
}
