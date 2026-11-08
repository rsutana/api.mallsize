<?php

namespace App\Services;

use App\Models\NewsPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class NewsPostService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function save(array $data, ?UploadedFile $cover, ?NewsPost $post = null): NewsPost
    {
        $post ??= new NewsPost;
        $removeCover = (bool) ($data['is_cover_removed'] ?? false);
        unset($data['is_cover_removed']);

        $post->fill($data);

        if (($cover !== null || $removeCover) && $post->cover_path) {
            Storage::disk('public')->delete($post->cover_path);
            $post->cover_path = null;
        }

        if ($cover !== null) {
            $path = $cover->store('news', 'public');

            if ($path === false) {
                throw new RuntimeException('The cover photo could not be saved.');
            }

            $post->cover_path = $path;
        }

        $post->save();

        return $post->refresh();
    }

    public function delete(NewsPost $post): void
    {
        if ($post->cover_path) {
            Storage::disk('public')->delete($post->cover_path);
        }

        $post->delete();
    }
}
