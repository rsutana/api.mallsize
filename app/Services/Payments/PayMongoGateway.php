<?php

namespace App\Services\Payments;

use App\Models\Lookups\PaymentMethod;
use App\Models\Payment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayMongo Payment Intents integration.
 *
 * @see https://developers.paymongo.com/docs/accepting-payments
 */
class PayMongoGateway implements PaymentGateway
{
    /**
     * Our payment method codes mapped to PayMongo payment method types.
     */
    private const TYPES = [
        PaymentMethod::CARD => 'card',
        PaymentMethod::GCASH => 'gcash',
        PaymentMethod::MAYA => 'paymaya',
        PaymentMethod::QRPH => 'qrph',
        PaymentMethod::SHOPEEPAY => 'shopee_pay',
    ];

    public function __construct(
        private readonly string $secretKey,
        private readonly ?string $publicKey,
        private readonly ?string $webhookSecret,
        private readonly string $baseUrl,
    ) {}

    public function name(): string
    {
        return 'paymongo';
    }

    public function publicKey(): ?string
    {
        return $this->publicKey;
    }

    public function charge(Payment $payment, ?string $paymentMethodId, string $returnUrl): GatewayResult
    {
        $type = self::TYPES[$payment->method] ?? null;

        if ($type === null) {
            return new GatewayResult(GatewayResult::FAILED, failureReason: 'This payment method is not available online.');
        }

        try {
            $intent = $this->client()->post('/payment_intents', ['data' => ['attributes' => [
                'amount' => $payment->amount,
                'currency' => config('mallsize.payments.currency'),
                'capture_type' => 'automatic',
                'payment_method_allowed' => [$type],
                'description' => 'MallSize Training Center '.$payment->reference_no,
                'statement_descriptor' => 'MallSize',
                'metadata' => ['reference_no' => $payment->reference_no],
            ]]])->throw()->json('data');

            if ($paymentMethodId === null) {
                $paymentMethodId = $this->client()->post('/payment_methods', ['data' => ['attributes' => [
                    'type' => $type,
                    'billing' => $this->billing($payment),
                ]]])->throw()->json('data.id');
            }

            $attached = $this->client()->post('/payment_intents/'.$intent['id'].'/attach', ['data' => ['attributes' => [
                'payment_method' => $paymentMethodId,
                'return_url' => $returnUrl,
            ]]])->throw()->json('data');

            return $this->toResult($attached);
        } catch (RequestException $e) {
            Log::warning('PayMongo charge failed', ['payment' => $payment->reference_no, 'response' => $e->response->json()]);

            return new GatewayResult(
                GatewayResult::FAILED,
                $intent['id'] ?? null,
                failureReason: (string) ($e->response->json('errors.0.detail') ?? 'The payment could not be started.'),
            );
        }
    }

    public function retrieve(Payment $payment): GatewayResult
    {
        if ($payment->provider_intent_id === null) {
            return new GatewayResult(GatewayResult::FAILED, failureReason: 'Payment was never started.');
        }

        $intent = $this->client()->get('/payment_intents/'.$payment->provider_intent_id)->throw()->json('data');

        return $this->toResult($intent);
    }

    public function verifyWebhookSignature(string $payload, ?string $signatureHeader): bool
    {
        if ($this->webhookSecret === null || $signatureHeader === null) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $piece) {
            [$key, $value] = array_pad(explode('=', $piece, 2), 2, '');
            $parts[trim($key)] = trim($value);
        }

        if (! isset($parts['t'])) {
            return false;
        }

        $expected = hash_hmac('sha256', $parts['t'].'.'.$payload, $this->webhookSecret);

        foreach (['li', 'te'] as $key) {
            if (! empty($parts[$key]) && hash_equals($expected, $parts[$key])) {
                return true;
            }
        }

        return false;
    }

    public function parseWebhook(array $payload): ?GatewayResult
    {
        $type = data_get($payload, 'data.attributes.type');
        $resource = data_get($payload, 'data.attributes.data');
        $intentId = data_get($resource, 'attributes.payment_intent_id');

        if (! is_string($intentId)) {
            return null;
        }

        return match ($type) {
            'payment.paid' => new GatewayResult(GatewayResult::SUCCEEDED, $intentId, data_get($resource, 'id')),
            'payment.failed' => new GatewayResult(GatewayResult::FAILED, $intentId, data_get($resource, 'id'), failureReason: data_get($resource, 'attributes.failed_message')),
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $intent
     */
    private function toResult(array $intent): GatewayResult
    {
        $status = data_get($intent, 'attributes.status');
        $paymentId = data_get($intent, 'attributes.payments.0.id');
        $nextAction = data_get($intent, 'attributes.next_action');

        return match ($status) {
            'succeeded' => new GatewayResult(GatewayResult::SUCCEEDED, $intent['id'], $paymentId),
            'awaiting_next_action' => new GatewayResult(GatewayResult::REQUIRES_ACTION, $intent['id'], nextAction: $this->nextAction($nextAction)),
            'processing' => new GatewayResult(GatewayResult::PROCESSING, $intent['id']),
            default => new GatewayResult(
                GatewayResult::FAILED,
                $intent['id'],
                failureReason: data_get($intent, 'attributes.last_payment_error.failed_message') ?? 'The payment did not go through.',
            ),
        };
    }

    /**
     * @param  array<string, mixed>|null  $action
     * @return array{type: string, url?: string|null, image_url?: string|null}|null
     */
    private function nextAction(?array $action): ?array
    {
        return match (data_get($action, 'type')) {
            'redirect' => ['type' => 'redirect', 'url' => data_get($action, 'redirect.url')],
            'consume_qr' => ['type' => 'qr', 'image_url' => data_get($action, 'code.image_url')],
            default => null,
        };
    }

    /**
     * @return array{name: string, email: string, phone: string|null}
     */
    private function billing(Payment $payment): array
    {
        $user = $payment->student->user;

        return ['name' => $payment->student->full_name, 'email' => $user->email, 'phone' => $user->mobile];
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withBasicAuth($this->secretKey, '')
            ->acceptJson()
            ->asJson()
            ->timeout(20);
    }
}
