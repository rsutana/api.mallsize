<?php

namespace App\Http\Resources;

use App\Models\Lookups\PaymentMethod;
use App\Services\Payments\PaymentGateway;
use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property PaymentGateway $resource
 */
class PaymentConfigResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'driver' => $this->resource->name(),
            'public_key' => $this->resource->publicKey(),
            'methods' => array_values(array_filter(
                Lookups::options('payment_methods'),
                fn (array $o): bool => in_array($o['code'], PaymentMethod::ONLINE, true),
            )),
        ];
    }
}
