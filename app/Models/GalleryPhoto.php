<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $tag
 * @property string|null $image_path
 * @property int $sort_order
 */
#[Fillable(['title', 'tag', 'image_path', 'sort_order'])]
class GalleryPhoto extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
        ];
    }
}
