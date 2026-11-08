<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\IndexNewsRequest;
use App\Http\Resources\NewsPostResource;
use App\Models\NewsPost;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NewsController extends Controller
{
    public function index(IndexNewsRequest $request): AnonymousResourceCollection
    {
        return NewsPostResource::collection(
            NewsPost::query()
                ->visible()
                ->when($request->validated('category'), fn ($q, $category) => $q->where('category', $category))
                ->orderByDesc('is_featured')
                ->orderByDesc('published_on')
                ->get(),
        );
    }

    public function show(NewsPost $newsPost): NewsPostResource
    {
        abort_unless(NewsPost::query()->visible()->whereKey($newsPost->id)->exists(), 404);

        return new NewsPostResource($newsPost);
    }
}
