<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentGateway;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PayMongoWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentGateway $gateway, PaymentService $payments): Response
    {
        abort_unless($gateway->verifyWebhookSignature($request->getContent(), $request->header('Paymongo-Signature')), Response::HTTP_UNAUTHORIZED);

        $result = $gateway->parseWebhook($request->json()->all());

        if ($result !== null) {
            $payments->handleWebhook($result);
        }

        return response()->noContent(Response::HTTP_OK);
    }
}
