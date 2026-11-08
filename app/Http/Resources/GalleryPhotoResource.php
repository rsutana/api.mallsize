<?php

namespace App\Http\Resources;

use App\Models\GalleryPhoto;
use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @mixin GalleryPhoto
 */
class GalleryPhotoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $url = match (true) {
            $this->image_path === null => null,
            Str::startsWith($this->image_path, ['http://', 'https://', '/']) => $this->image_path,
            default => Storage::disk('public')->url($this->image_path),
        };

        return [
            'id' => $this->id,
            'title' => $this->title,
            'tag' => $this->tag,
            'tag_label' => Lookups::label('gallery_tags', $this->tag),
            'image_url' => $url,
        ];
    }
}
