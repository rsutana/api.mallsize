<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BalanceReminderResource;
use App\Services\BalanceReminderService;

class BalanceReminderController extends Controller
{
    public function store(BalanceReminderService $reminders): BalanceReminderResource
    {
        return new BalanceReminderResource($reminders->sendAll());
    }
}
