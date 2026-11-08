<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inquiry\StoreInquiryRequest;
use App\Models\Inquiry;
use App\Notifications\InquiryReceivedNotification;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;

class InquiryController extends Controller
{
    public function store(StoreInquiryRequest $request): Response
    {
        $inquiry = Inquiry::query()->create($request->validated());

        Notification::route('mail', config('mallsize.staff_notification_email'))
            ->notify(new InquiryReceivedNotification($inquiry));

        return response()->noContent(Response::HTTP_CREATED);
    }
}
