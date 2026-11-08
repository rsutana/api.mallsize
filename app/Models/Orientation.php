<?php

namespace App\Models;

use App\Models\Lookups\PostStatus;
use Carbon\CarbonImmutable;
use Database\Factories\OrientationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $title
 * @property int|null $course_id
 * @property string|null $details
 * @property CarbonImmutable $date
 * @property string|null $time_label
 * @property string $format
 * @property string|null $venue
 * @property bool $is_social_linked
 * @property string|null $social_platform
 * @property string|null $social_url
 * @property string $link_mode
 * @property string $status
 * @property bool $is_notify_students
 * @property CarbonImmutable|null $notified_at
 * @property-read Course|null $course
 */
#[Fillable(['title', 'course_id', 'details', 'date', 'time_label', 'format', 'venue', 'is_social_linked', 'social_platform', 'social_url', 'link_mode', 'status', 'is_notify_students', 'notified_at'])]
class Orientation extends Model
{
    /** @use HasFactory<OrientationFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_social_linked' => 'boolean',
            'is_notify_students' => 'boolean',
            'notified_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', PostStatus::PUBLISHED);
    }
}
