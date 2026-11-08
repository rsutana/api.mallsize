<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Resources\CheckoutResource;
use App\Http\Resources\PaymentResource;
use App\Models\Lookups\PaymentStatus;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    /**
     * Payment history (failed attempts are left out).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return PaymentResource::collection(
            $request->user()->student->payments()
                ->where('status', '!=', PaymentStatus::FAILED)
                ->with('allocations.enrollment.course')
                ->latest()
                ->get(),
        );
    }

    public function store(StorePaymentRequest $request, PaymentService $payments): JsonResponse
    {
        $result = $payments->checkout(
            $request->user()->student,
            $request->validated('items'),
            $request->validated('method'),
            $request->validated('payment_method_id'),
            rtrim((string) config('app.frontend_url'), '/').'/payments/return',
        );

        return (new CheckoutResource($result))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Current state of a payment; re-checks the gateway while pending.
     */
    public function show(Payment $payment, PaymentService $payments): PaymentResource
    {
        Gate::authorize('view', $payment);

        return new PaymentResource($payments->refresh($payment)->load('allocations.enrollment.course'));
    }
}
