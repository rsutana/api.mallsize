<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('reset link points to the SPA', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->withHeaders(spaHeaders())->postJson('/forgot-password', ['email' => $user->email])->assertOk();

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
        $url = $notification->toMail($user)->actionUrl;

        return str_starts_with($url, config('app.frontend_url').'/reset-password?token=');
    });
});

test('password can be reset with a valid token', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->withHeaders(spaHeaders())->postJson('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
        $this->withHeaders(spaHeaders())->postJson('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk();

        return true;
    });

    expect(password_verify('new-password', $user->refresh()->password))->toBeTrue();
});

test('signed-in users can change their password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->withHeaders(spaHeaders())->putJson('/user/password', [
        'current_password' => 'password',
        'password' => 'brand-new-pass',
        'password_confirmation' => 'brand-new-pass',
    ])->assertOk();

    expect(password_verify('brand-new-pass', $user->refresh()->password))->toBeTrue();
});
