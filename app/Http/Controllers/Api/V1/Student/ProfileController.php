<?php

namespace App\Http\Controllers\Api\V1\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request): UserResource
    {
        $user = $request->user();
        $data = $request->validated();

        DB::transaction(function () use ($user, $data): void {
            $user->update([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'is_email_notifications' => $data['is_email_notifications'],
                'is_sms_notifications' => $data['is_sms_notifications'],
            ]);

            $user->student->update(collect($data)->only(['first_name', 'middle_name', 'last_name', 'birthdate', 'sex', 'education', 'address'])->all());
        });

        return new UserResource($user->refresh()->load('student'));
    }
}
