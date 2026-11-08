<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Str;

/**
 * Local/testing gateway: every charge succeeds immediately, except card
 * payments using the payment method id "pm_fake_declined".
 */
class FakeGateway implements PaymentGateway
{
    public const DECLINED_METHOD = 'pm_fake_declined';

    public function name(): string
    {
        return 'fake';
    }

    public function publicKey(): ?string
    {
        return null;
    }

    public function charge(Payment $payment, ?string $paymentMethodId, string $returnUrl): GatewayResult
    {
        $intentId = 'fake_pi_'.Str::lower(Str::random(20));

        if ($paymentMethodId === self::DECLINED_METHOD) {
            return new GatewayResult(GatewayResult::FAILED, $intentId, failureReason: 'Your card was declined.');
        }

        return new GatewayResult(GatewayResult::SUCCEEDED, $intentId, 'fake_pay_'.Str::lower(Str::random(20)));
    }

    public function retrieve(Payment $payment): GatewayResult
    {
        return new GatewayResult(
            $payment->isSettled() ? GatewayResult::SUCCEEDED : GatewayResult::PROCESSING,
            $payment->provider_intent_id,
            $payment->provider_payment_id,
        );
    }

    public function verifyWebhookSignature(string $payload, ?string $signatureHeader): bool
    {
        return true;
    }

    public function parseWebhook(array $payload): ?GatewayResult
    {
        $intentId = data_get($payload, 'intent_id');

        if (! is_string($intentId)) {
            return null;
        }

        return new GatewayResult(
            data_get($payload, 'status') === 'failed' ? GatewayResult::FAILED : GatewayResult::SUCCEEDED,
            $intentId,
        );
    }
}
