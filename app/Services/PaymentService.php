<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Lookups\EnrollmentStatus;
use App\Models\Lookups\PaymentAmountType;
use App\Models\Lookups\PaymentStatus;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use App\Notifications\PaymentReceivedNotification;
use App\Notifications\PaymentReviewedNotification;
use App\Services\Payments\GatewayResult;
use App\Services\Payments\PaymentGateway;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly PaymentGateway $gateway,
        private readonly EnrollmentService $enrollments,
        private readonly ReferenceNumberService $references,
        private readonly NotificationLogService $log,
    ) {}

    /**
     * Create a payment for one or more of the student's enrollments and hand
     * it to the gateway. Amounts are always computed here, never trusted
     * from the client.
     *
     * @param  list<array{enrollment_id: int, amount_type: string, amount?: int|null}>  $items
     * @return array{payment: Payment, next_action: array<string, mixed>|null}
     *
     * @throws ValidationException
     */
    public function checkout(Student $student, array $items, string $method, ?string $paymentMethodId, string $returnUrl): array
    {
        $payment = DB::transaction(function () use ($student, $items, $method): Payment {
            $ids = array_map(fn (array $item): int => (int) $item['enrollment_id'], $items);

            $enrollments = Enrollment::query()
                ->whereIn('id', $ids)
                ->where('student_id', $student->id)
                ->active()
                ->with('course')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($enrollments->count() !== count(array_unique($ids))) {
                throw ValidationException::withMessages(['items' => 'One of the selected courses cannot be paid.']);
            }

            $lines = [];
            foreach ($items as $i => $item) {
                $enrollment = $enrollments[(int) $item['enrollment_id']];
                $lines[] = [$enrollment, ...$this->lineAmount($enrollment, $item, "items.{$i}")];
            }

            $payment = $student->payments()->create([
                'reference_no' => $this->references->nextReceiptReference(),
                'amount' => array_sum(array_column($lines, 1)),
                'method' => $method,
                'status' => PaymentStatus::PENDING,
                'provider' => $this->gateway->name(),
            ]);

            foreach ($lines as [$enrollment, $amount, $type]) {
                $payment->allocations()->create([
                    'enrollment_id' => $enrollment->id,
                    'amount' => $amount,
                    'amount_type' => $type,
                ]);
                $this->enrollments->extendHold($enrollment);
            }

            return $payment;
        });

        $result = $this->gateway->charge($payment->load('student.user'), $paymentMethodId, $returnUrl);

        $payment->forceFill(['provider_intent_id' => $result->intentId])->save();
        $this->apply($payment, $result);

        return ['payment' => $payment->refresh(), 'next_action' => $result->succeeded() || $result->failed() ? null : $result->nextAction];
    }

    /**
     * Re-check a pending payment with the gateway (used by the return page).
     */
    public function refresh(Payment $payment): Payment
    {
        if ($payment->status === PaymentStatus::PENDING && $payment->provider_intent_id) {
            $this->apply($payment, $this->gateway->retrieve($payment));
        }

        return $payment->refresh();
    }

    public function handleWebhook(GatewayResult $result): void
    {
        $payment = Payment::query()->where('provider_intent_id', $result->intentId)->first();

        if ($payment !== null) {
            $this->apply($payment, $result);
        }
    }

    public function markPaid(Payment $payment, ?string $providerPaymentId = null): void
    {
        $changed = DB::transaction(function () use ($payment, $providerPaymentId): bool {
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($locked->status !== PaymentStatus::PENDING) {
                return false;
            }

            $locked->forceFill([
                'status' => PaymentStatus::PAID,
                'paid_at' => now(),
                'provider_payment_id' => $providerPaymentId ?? $locked->provider_payment_id,
                'failure_reason' => null,
            ])->save();

            foreach ($locked->allocations()->with('enrollment')->get() as $allocation) {
                $this->enrollments->activate($allocation->enrollment);
            }

            return true;
        });

        if ($changed) {
            $payment->refresh();
            $payment->student->user->notify(new PaymentReceivedNotification($payment));
            $this->log->record('Payment received — '.$payment->student->full_name.' '.Money::format($payment->amount));
        }
    }

    public function markFailed(Payment $payment, ?string $reason): void
    {
        Payment::query()
            ->whereKey($payment->id)
            ->where('status', PaymentStatus::PENDING)
            ->update(['status' => PaymentStatus::FAILED, 'failure_reason' => $reason, 'updated_at' => now()]);
    }

    /**
     * @throws ValidationException
     */
    public function verify(Payment $payment, User $staff): Payment
    {
        if ($payment->status !== PaymentStatus::PAID) {
            throw ValidationException::withMessages(['payment' => 'Only paid payments can be verified.']);
        }

        $payment->forceFill([
            'status' => PaymentStatus::VERIFIED,
            'verified_at' => now(),
            'verified_by' => $staff->id,
        ])->save();

        $payment->student->user->notify(new PaymentReviewedNotification($payment));
        $this->log->record('Payment verified — '.$payment->student->full_name.' '.Money::format($payment->amount));

        return $payment;
    }

    /**
     * @throws ValidationException
     */
    public function reject(Payment $payment, User $staff, ?string $reason): Payment
    {
        if (! in_array($payment->status, [PaymentStatus::PENDING, PaymentStatus::PAID], true)) {
            throw ValidationException::withMessages(['payment' => 'This payment can no longer be rejected.']);
        }

        $payment->forceFill([
            'status' => PaymentStatus::REJECTED,
            'rejected_reason' => $reason,
            'verified_at' => now(),
            'verified_by' => $staff->id,
        ])->save();

        $payment->student->user->notify(new PaymentReviewedNotification($payment));
        $this->log->record('Payment rejected — '.$payment->student->full_name);

        return $payment;
    }

    private function apply(Payment $payment, GatewayResult $result): void
    {
        if ($result->succeeded()) {
            $this->markPaid($payment, $result->providerPaymentId);
        } elseif ($result->failed()) {
            $this->markFailed($payment, $result->failureReason);
        }
    }

    /**
     * @param  array{enrollment_id: int, amount_type: string, amount?: int|null}  $item
     * @return array{0: int, 1: string}
     *
     * @throws ValidationException
     */
    private function lineAmount(Enrollment $enrollment, array $item, string $key): array
    {
        $paid = $enrollment->amountPaid();
        $balance = max(0, $enrollment->fee - $paid);
        $name = $enrollment->course->name;

        if ($balance === 0) {
            throw ValidationException::withMessages([$key => $name.' is already fully paid.']);
        }

        if ($enrollment->status === EnrollmentStatus::PENDING_PAYMENT && $enrollment->expires_at?->isPast()) {
            throw ValidationException::withMessages([$key => 'Your reservation for '.$name.' expired. Please enroll again.']);
        }

        $minimum = $paid === 0
            ? min($enrollment->min_down_payment, $balance)
            : min((int) config('mallsize.payments.min_partial_amount'), $balance);

        return match ($item['amount_type']) {
            'down_payment' => $paid === 0
                ? [$minimum, $minimum === $balance ? PaymentAmountType::FULL : PaymentAmountType::DOWN_PAYMENT]
                : throw ValidationException::withMessages([$key => 'The down payment for '.$name.' is already paid.']),
            'full' => [$balance, $paid === 0 ? PaymentAmountType::FULL : PaymentAmountType::BALANCE],
            'custom' => $this->customAmount((int) ($item['amount'] ?? 0), $minimum, $balance, $paid, $key),
            default => throw ValidationException::withMessages([$key => 'Choose how much to pay.']),
        };
    }

    /**
     * @return array{0: int, 1: string}
     *
     * @throws ValidationException
     */
    private function customAmount(int $amount, int $minimum, int $balance, int $paid, string $key): array
    {
        if ($amount < $minimum || $amount > $balance) {
            throw ValidationException::withMessages([$key.'.amount' => 'Enter an amount between '.Money::format($minimum).' and '.Money::format($balance).'.']);
        }

        $type = match (true) {
            $amount === $balance => $paid === 0 ? PaymentAmountType::FULL : PaymentAmountType::BALANCE,
            $paid === 0 => PaymentAmountType::DOWN_PAYMENT,
            default => PaymentAmountType::PARTIAL,
        };

        return [$amount, $type];
    }
}
