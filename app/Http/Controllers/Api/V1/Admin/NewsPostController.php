<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsPost\IndexNewsPostRequest;
use App\Http\Requests\NewsPost\SaveNewsPostRequest;
use App\Http\Resources\NewsPostResource;
use App\Models\NewsPost;
use App\Services\NewsPostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NewsPostController extends Controller
{
    public const PER_PAGE = 15;

    public function index(IndexNewsPostRequest $request): AnonymousResourceCollection
    {
        return NewsPostResource::collection(
            NewsPost::query()
                ->orderByDesc('published_on')
                ->orderByDesc('id')
                ->paginate($request->integer('per_page', self::PER_PAGE)),
        );
    }

    public function store(SaveNewsPostRequest $request, NewsPostService $posts): JsonResponse
    {
        return (new NewsPostResource($posts->save($request->safe()->except('cover'), $request->file('cover'))))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(NewsPost $newsPost): NewsPostResource
    {
        return new NewsPostResource($newsPost);
    }

    public function update(SaveNewsPostRequest $request, NewsPost $newsPost, NewsPostService $posts): NewsPostResource
    {
        return new NewsPostResource($posts->save($request->safe()->except('cover'), $request->file('cover'), $newsPost));
    }

    public function destroy(NewsPost $newsPost, NewsPostService $posts): Response
    {
        $posts->delete($newsPost);

        return response()->noContent();
    }
}
