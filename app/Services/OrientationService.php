<?php

namespace App\Services;

use App\Models\Lookups\EnrollmentStatus;
use App\Models\Lookups\PostStatus;
use App\Models\Orientation;
use App\Models\User;
use App\Notifications\OrientationPublishedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;

class OrientationService
{
    public function __construct(private readonly NotificationLogService $log) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function save(array $data, ?Orientation $orientation = null): Orientation
    {
        $orientation ??= new Orientation;

        if (! ($data['is_social_linked'] ?? false)) {
            $data['social_platform'] = null;
            $data['social_url'] = null;
        }

        $orientation->fill($data)->save();

        $this->notifyIfPublished($orientation);

        return $orientation->refresh();
    }

    /**
     * Email/SMS enrolled students of the orientation's course (or all
     * enrolled students) the first time it is published with notify on.
     */
    private function notifyIfPublished(Orientation $orientation): void
    {
        if ($orientation->status !== PostStatus::PUBLISHED || ! $orientation->is_notify_students || $orientation->notified_at !== null) {
            return;
        }

        $users = User::query()
            ->whereHas('student.enrollments', function (Builder $q) use ($orientation): void {
                $q->where('status', EnrollmentStatus::ENROLLED)
                    ->when($orientation->course_id, fn (Builder $q) => $q->where('course_id', $orientation->course_id));
            })
            ->get();

        Notification::send($users, new OrientationPublishedNotification($orientation));

        $orientation->forceFill(['notified_at' => now()])->save();
        $this->log->record('Orientation posted — '.$orientation->title, $users->count());
    }
}
