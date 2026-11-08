<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Registration\StoreRegistrationRequest;
use App\Http\Resources\RegistrationResource;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    /**
     * Create the student account and slot reservations, then sign in.
     */
    public function store(StoreRegistrationRequest $request, RegistrationService $registrations): JsonResponse
    {
        $result = $registrations->register($request->validated());

        Auth::guard('web')->login($result['user']);
        $request->session()->regenerate();

        return (new RegistrationResource($result))->response()->setStatusCode(Response::HTTP_CREATED);
    }
}
