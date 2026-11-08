<?php

use App\Models\Enrollment;
use App\Models\Lookups\EnrollmentStatus;
use App\Models\Lookups\PaymentStatus;
use App\Models\Payment;
use App\Notifications\PaymentReceivedNotification;
use App\Services\Payments\FakeGateway;
use App\Services\Payments\GatewayResult;
use App\Services\Payments\PaymentGateway;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    $this->student = makeStudent();
    $this->enrollment = Enrollment::factory()->pending()->create(['student_id' => $this->student->id]);
});

function checkout(array $items, string $method = 'gcash', ?string $pm = null)
{
    return test()->actingAs(test()->student->user)->postJson('/api/v1/student/payments', array_filter([
        'method' => $method,
        'payment_method_id' => $pm,
        'items' => $items,
    ]));
}

test('paying the down payment enrolls the student and sends a receipt', function () {
    checkout([['enrollment_id' => $this->enrollment->id, 'amount_type' => 'down_payment']])
        ->assertCreated()
        ->assertJsonPath('data.payment.amount', 500_000)
        ->assertJsonPath('data.payment.status', PaymentStatus::PAID)
        ->assertJsonPath('data.payment.allocations.0.amount_type', 'down_payment')
        ->assertJsonPath('data.next_action', null);

    expect($this->enrollment->refresh()->status)->toBe(EnrollmentStatus::ENROLLED)
        ->and($this->enrollment->balance())->toBe(1_750_000);
    Notification::assertSentTo($this->student->user, PaymentReceivedNotification::class);
});

test('full payment settles the whole fee', function () {
    checkout([['enrollment_id' => $this->enrollment->id, 'amount_type' => 'full']], 'card', 'pm_ok')
        ->assertCreated()
        ->assertJsonPath('data.payment.amount', 2_250_000);

    expect($this->enrollment->refresh()->balance())->toBe(0);
});

test('custom amounts respect the minimum and the balance', function () {
    checkout([['enrollment_id' => $this->enrollment->id, 'amount_type' => 'custom', 'amount' => 100_000]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items.0.amount');

    checkout([['enrollment_id' => $this->enrollment->id, 'amount_type' => 'custom', 'amount' => 9_999_999]])
        ->assertUnprocessable();

    checkout([['enrollment_id' => $this->enrollment->id, 'amount_type' => 'custom', 'amount' => 600_000]])
        ->assertCreated();

    checkout([['enrollment_id' => $this->enrollment->id, 'amount_type' => 'custom', 'amount' => 50_000]])
        ->assertUnprocessable();

    checkout([['enrollment_id' => $this->enrollment->id, 'amount_type' => 'custom', 'amount' => 100_000]])
        ->assertCreated()
        ->assertJsonPath('data.payment.allocations.0.amount_type', 'partial');
});

test('a down payment cannot be paid twice', function () {
    checkout([['enrollment_id' => $this->enrollment->id, 'amount_type' => 'down_payment']])->assertCreated();
    checkout([['enrollment_id' => $this->enrollment->id, 'amount_type' => 'down_payment']])->assertUnprocessable();
});

test('one payment can cover several courses', function () {
    $second = Enrollment::factory()->pending()->create(['student_id' => $this->student->id]);

    checkout([
        ['enrollment_id' => $this->enrollment->id, 'amount_type' => 'down_payment'],
        ['enrollment_id' => $second->id, 'amount_type' => 'full'],
    ])->assertCreated()->assertJsonPath('data.payment.amount', 500_000 + 2_250_000);

    expect($second->refresh()->status)->toBe(EnrollmentStatus::ENROLLED);
});

test('students cannot pay someone else’s enrollment', function () {
    $other = Enrollment::factory()->pending()->create();

    checkout([['enrollment_id' => $other->id, 'amount_type' => 'full']])->assertUnprocessable();
});

test('declined cards leave the enrollment pending', function () {
    checkout([['enrollment_id' => $this->enrollment->id, 'amount_type' => 'full']], 'card', FakeGateway::DECLINED_METHOD)
        ->assertCreated()
        ->assertJsonPath('data.payment.status', PaymentStatus::FAILED);

    expect($this->enrollment->refresh()->status)->toBe(EnrollmentStatus::PENDING_PAYMENT);
});

test('bank transfer is not offered online', function () {
    checkout([['enrollment_id' => $this->enrollment->id, 'amount_type' => 'full']], 'bank_transfer')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('method');
});

test('marking a payment paid is idempotent', function () {
    $payment = Payment::factory()->pending()->create(['student_id' => $this->student->id]);
    $payment->allocations()->create(['enrollment_id' => $this->enrollment->id, 'amount' => 500_000, 'amount_type' => 'down_payment']);

    $service = app(PaymentService::class);
    $service->markPaid($payment);
    $service->markPaid($payment->refresh());
    $service->handleWebhook(new GatewayResult(GatewayResult::SUCCEEDED, $payment->provider_intent_id));

    Notification::assertSentToTimes($this->student->user, PaymentReceivedNotification::class, 1);
    expect($this->enrollment->refresh()->status)->toBe(EnrollmentStatus::ENROLLED);
});

test('students can only view their own payments', function () {
    $mine = Payment::factory()->create(['student_id' => $this->student->id]);
    $theirs = Payment::factory()->create();

    $this->actingAs($this->student->user)->getJson('/api/v1/student/payments/'.$mine->id)->assertOk();
    $this->actingAs($this->student->user)->getJson('/api/v1/student/payments/'.$theirs->id)->assertForbidden();
});

test('the dashboard summarises fees, payments and balances', function () {
    checkout([['enrollment_id' => $this->enrollment->id, 'amount_type' => 'down_payment']]);

    $this->actingAs($this->student->user)->getJson('/api/v1/student/dashboard')
        ->assertOk()
        ->assertJsonPath('data.total_fee', 2_250_000)
        ->assertJsonPath('data.total_paid', 500_000)
        ->assertJsonPath('data.total_balance', 1_750_000)
        ->assertJsonPath('data.with_balance_count', 1)
        ->assertJsonPath('data.enrollments.0.payment_status', 'down_payment_only');
});

test('paymongo webhooks require a valid signature', function () {
    config(['mallsize.payments.driver' => 'paymongo', 'mallsize.payments.paymongo.secret_key' => 'sk_test', 'mallsize.payments.paymongo.webhook_secret' => 'whsec']);
    app()->forgetInstance(PaymentGateway::class);

    $payment = Payment::factory()->pending()->create(['student_id' => $this->student->id, 'provider_intent_id' => 'pi_123']);
    $payment->allocations()->create(['enrollment_id' => $this->enrollment->id, 'amount' => 500_000, 'amount_type' => 'down_payment']);

    $body = json_encode(['data' => ['attributes' => ['type' => 'payment.paid', 'data' => ['id' => 'pay_1', 'attributes' => ['payment_intent_id' => 'pi_123']]]]]);

    $this->call('POST', '/api/v1/webhooks/paymongo', server: ['CONTENT_TYPE' => 'application/json', 'HTTP_PAYMONGO_SIGNATURE' => 't=1,te=bad'], content: $body)
        ->assertUnauthorized();

    $signature = 't=1,te='.hash_hmac('sha256', '1.'.$body, 'whsec');
    $this->call('POST', '/api/v1/webhooks/paymongo', server: ['CONTENT_TYPE' => 'application/json', 'HTTP_PAYMONGO_SIGNATURE' => $signature], content: $body)
        ->assertOk();

    expect($payment->refresh()->status)->toBe(PaymentStatus::PAID)
        ->and($payment->provider_payment_id)->toBe('pay_1');
});
