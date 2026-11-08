<?php

namespace App\Http\Resources;

use App\Models\NewsPost;
use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin NewsPost
 */
class NewsPostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category,
            'category_label' => Lookups::label('news_categories', $this->category),
            'summary' => $this->summary,
            'body' => $this->body,
            'cover_url' => $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null,
            'status' => $this->status,
            'status_label' => Lookups::label('post_statuses', $this->status),
            'published_on' => $this->published_on?->toDateString(),
            'is_featured' => $this->is_featured,
        ];
    }
}
