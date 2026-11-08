<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\IndexGalleryPhotoRequest;
use App\Http\Resources\GalleryPhotoResource;
use App\Models\GalleryPhoto;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GalleryPhotoController extends Controller
{
    public function index(IndexGalleryPhotoRequest $request): AnonymousResourceCollection
    {
        return GalleryPhotoResource::collection(
            GalleryPhoto::query()
                ->when($request->validated('tag'), fn ($q, $tag) => $q->where('tag', $tag))
                ->orderBy('sort_order')
                ->get(),
        );
    }
}
