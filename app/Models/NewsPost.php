<?php

namespace App\Models;

use App\Models\Lookups\PostStatus;
use Carbon\CarbonImmutable;
use Database\Factories\NewsPostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $category
 * @property string|null $summary
 * @property string|null $body
 * @property string|null $cover_path
 * @property string $status
 * @property CarbonImmutable|null $published_on
 * @property bool $is_featured
 */
#[Fillable(['title', 'slug', 'category', 'summary', 'body', 'cover_path', 'status', 'published_on', 'is_featured'])]
class NewsPost extends Model
{
    /** @use HasFactory<NewsPostFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (NewsPost $post): void {
            if (blank($post->slug) || $post->isDirty('title')) {
                $base = Str::slug($post->title) ?: 'post';
                $slug = $base;
                $i = 2;
                while (static::query()->where('slug', $slug)->when($post->id, fn (Builder $q) => $q->whereKeyNot($post->id))->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $post->slug = $slug;
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_on' => 'date',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Published posts, plus scheduled posts whose publish date has arrived.
     *
     * @param  Builder<self>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where(function (Builder $q): void {
            $q->where('status', PostStatus::PUBLISHED)
                ->orWhere(fn (Builder $q) => $q->where('status', PostStatus::SCHEDULED)->whereDate('published_on', '<=', today()));
        })->where(fn (Builder $q) => $q->whereNull('published_on')->orWhereDate('published_on', '<=', today()));
    }
}
