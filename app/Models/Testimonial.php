<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $quote
 * @property string $student_name
 * @property string $course_label
 * @property int $rating
 * @property bool $is_published
 * @property int $sort_order
 */
#[Fillable(['quote', 'student_name', 'course_label', 'rating', 'is_published', 'sort_order'])]
class Testimonial extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_published' => 'boolean',
        ];
    }
}
