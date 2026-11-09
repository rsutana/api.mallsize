<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enrollment\IndexAdminEnrollmentRequest;
use App\Http\Resources\EnrollmentResource;
use App\Services\EnrollmentExportService;
use App\Services\EnrollmentQueryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnrollmentController extends Controller
{
    public const PER_PAGE = 25;

    public function index(IndexAdminEnrollmentRequest $request, EnrollmentQueryService $query): AnonymousResourceCollection
    {
        return EnrollmentResource::collection(
            $query->filtered($request->validated())->paginate($request->integer('per_page', self::PER_PAGE)),
        );
    }

    /**
     * CSV of the enrollments matching the same filters as the table.
     */
    public function export(IndexAdminEnrollmentRequest $request, EnrollmentQueryService $query, EnrollmentExportService $export): StreamedResponse
    {
        return $export->download($query->filtered($request->validated()));
    }
}
