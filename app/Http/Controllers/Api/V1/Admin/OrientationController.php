<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orientation\IndexOrientationRequest;
use App\Http\Requests\Orientation\SaveOrientationRequest;
use App\Http\Resources\OrientationResource;
use App\Models\Orientation;
use App\Services\OrientationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class OrientationController extends Controller
{
    public const PER_PAGE = 15;

    public function index(IndexOrientationRequest $request): AnonymousResourceCollection
    {
        return OrientationResource::collection(
            Orientation::query()
                ->with('course')
                ->orderBy('date')
                ->orderBy('id')
                ->paginate($request->integer('per_page', self::PER_PAGE)),
        );
    }

    public function store(SaveOrientationRequest $request, OrientationService $orientations): JsonResponse
    {
        return (new OrientationResource($orientations->save($request->validated())->load('course')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Orientation $orientation): OrientationResource
    {
        return new OrientationResource($orientation->load('course'));
    }

    public function update(SaveOrientationRequest $request, Orientation $orientation, OrientationService $orientations): OrientationResource
    {
        return new OrientationResource($orientations->save($request->validated(), $orientation)->load('course'));
    }

    public function destroy(Orientation $orientation): Response
    {
        $orientation->delete();

        return response()->noContent();
    }
}
