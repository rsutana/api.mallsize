<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentConfigResource;
use App\Services\Payments\PaymentGateway;

class PaymentConfigController extends Controller
{
    public function __invoke(PaymentGateway $gateway): PaymentConfigResource
    {
        return new PaymentConfigResource($gateway);
    }
}
