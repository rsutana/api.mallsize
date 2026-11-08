<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

test('users can log in through the SPA endpoint', function () {
    $user = User::factory()->create();

    $this->withHeaders(spaHeaders())
        ->postJson('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertOk();

    $this->assertAuthenticatedAs($user);
});

test('users can not log in with an invalid password', function () {
    $user = User::factory()->create();

    $this->withHeaders(spaHeaders())
        ->postJson('/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    $this->assertGuest();
});

test('users can log out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->withHeaders(spaHeaders())->postJson('/logout')->assertNoContent();

    $this->assertGuest('web');
});

test('login attempts are rate limited', function () {
    $user = User::factory()->create();

    RateLimiter::increment(md5('login'.implode('|', [$user->email, '127.0.0.1'])), amount: 5);

    $this->withHeaders(spaHeaders())
        ->postJson('/login', ['email' => $user->email, 'password' => 'wrong-password'])
        ->assertTooManyRequests();
});

test('the identity endpoint returns the user with role and student profile', function () {
    $student = makeStudent();

    $this->actingAs($student->user)
        ->getJson('/api/v1/user')
        ->assertOk()
        ->assertJsonPath('role', 'student')
        ->assertJsonPath('student.reference_no', $student->reference_no)
        ->assertJsonMissingPath('data');
});

test('guests get 401 from the identity endpoint', function () {
    $this->getJson('/api/v1/user')->assertUnauthorized();
});
