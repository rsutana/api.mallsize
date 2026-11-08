<?php

namespace App\Services\Payments;

/**
 * Normalised outcome of a gateway call.
 */
final readonly class GatewayResult
{
    public const SUCCEEDED = 'succeeded';

    public const REQUIRES_ACTION = 'requires_action';

    public const PROCESSING = 'processing';

    public const FAILED = 'failed';

    /**
     * @param  array{type: string, url?: string|null, image_url?: string|null}|null  $nextAction
     */
    public function __construct(
        public string $status,
        public ?string $intentId = null,
        public ?string $providerPaymentId = null,
        public ?array $nextAction = null,
        public ?string $failureReason = null,
    ) {}

    public function succeeded(): bool
    {
        return $this->status === self::SUCCEEDED;
    }

    public function failed(): bool
    {
        return $this->status === self::FAILED;
    }
}
