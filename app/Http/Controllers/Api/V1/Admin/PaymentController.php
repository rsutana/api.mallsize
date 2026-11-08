<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\IndexAdminPaymentRequest;
use App\Http\Requests\Payment\RejectPaymentRequest;
use App\Http\Requests\Payment\VerifyPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Lookups\PaymentStatus;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    /**
     * Payments to reconcile. Defaults to paid-but-unverified ("To verify").
     */
    public function index(IndexAdminPaymentRequest $request): AnonymousResourceCollection
    {
        $status = $request->validated('status') ?? PaymentStatus::PAID;

        return PaymentResource::collection(
            Payment::query()
                ->when($status !== 'all', fn ($q) => $q->where('status', $status))
                ->with(['student.user', 'allocations.enrollment.course'])
                ->latest()
                ->paginate($request->integer('per_page', 25)),
        );
    }

    public function verify(VerifyPaymentRequest $request, Payment $payment, PaymentService $payments): PaymentResource
    {
        return new PaymentResource($payments->verify($payment, $request->user())->load(['student.user', 'allocations.enrollment.course']));
    }

    public function reject(RejectPaymentRequest $request, Payment $payment, PaymentService $payments): PaymentResource
    {
        return new PaymentResource($payments->reject($payment, $request->user(), $request->validated('reason'))->load(['student.user', 'allocations.enrollment.course']));
    }
}
