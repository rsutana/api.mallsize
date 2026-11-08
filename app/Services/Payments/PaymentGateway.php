<?php

namespace App\Services\Payments;

use App\Models\Payment;

interface PaymentGateway
{
    /**
     * Driver name stored on payments (e.g. "fake", "paymongo").
     */
    public function name(): string;

    /**
     * Publishable key the browser uses to tokenize cards, if any.
     */
    public function publicKey(): ?string;

    /**
     * Start charging a payment. Card payments pass the browser-created
     * payment method id; wallets and QR Ph create one server-side.
     */
    public function charge(Payment $payment, ?string $paymentMethodId, string $returnUrl): GatewayResult;

    /**
     * Fetch the current state of a previously started charge.
     */
    public function retrieve(Payment $payment): GatewayResult;

    public function verifyWebhookSignature(string $payload, ?string $signatureHeader): bool;

    /**
     * Turn a webhook payload into a result carrying the intent id, or null
     * when the event is not relevant.
     *
     * @param  array<string, mixed>  $payload
     */
    public function parseWebhook(array $payload): ?GatewayResult;
}
