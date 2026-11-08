<?php

use App\Models\BatchSet;
use App\Models\Enrollment;
use App\Models\Location;
use App\Models\Lookups\EnrollmentStatus;
use App\Models\User;

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'email' => 'maria.santos@email.com',
        'mobile' => '0917 888 0151',
        'password' => 'secret-pass',
        'password_confirmation' => 'secret-pass',
        'first_name' => 'Maria',
        'last_name' => 'Santos',
        'sex' => 'female',
        'education' => 'college',
        'location_id' => Location::factory()->create()->id,
        'set_ids' => [makeSet()->id],
        'is_email_notifications' => true,
        'is_sms_notifications' => false,
        'is_agreed' => true,
    ], $overrides);
}

test('registration creates the account, student profile and pending enrollments and signs in', function () {
    $response = $this->withHeaders(spaHeaders())->postJson('/api/v1/registrations', registrationPayload());

    $response->assertCreated()
        ->assertJsonPath('data.user.role', 'student')
        ->assertJsonPath('data.user.student.first_name', 'Maria')
        ->assertJsonPath('data.enrollments.0.status', EnrollmentStatus::PENDING_PAYMENT)
        ->assertJsonPath('data.enrollments.0.balance', 2_250_000);

    $user = User::query()->where('email', 'maria.santos@email.com')->firstOrFail();
    expect($user->student->reference_no)->toStartWith('MS-'.now()->year.'-')
        ->and($user->is_sms_notifications)->toBeFalse();
    $this->assertAuthenticatedAs($user);
});

test('registration rejects a fully booked set', function () {
    $set = makeSet(['slots' => 1]);
    Enrollment::factory()->create(['batch_set_id' => $set->id]);

    $this->withHeaders(spaHeaders())
        ->postJson('/api/v1/registrations', registrationPayload(['set_ids' => [$set->id]]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('set_ids');

    expect(User::query()->where('email', 'maria.santos@email.com')->exists())->toBeFalse();
});

test('expired reservations free their slot', function () {
    $set = makeSet(['slots' => 1]);
    Enrollment::factory()->pending()->create(['batch_set_id' => $set->id, 'expires_at' => now()->subMinute()]);

    $this->withHeaders(spaHeaders())
        ->postJson('/api/v1/registrations', registrationPayload(['set_ids' => [$set->id]]))
        ->assertCreated();
});

test('registration rejects two sets of the same course', function () {
    $set = makeSet();
    $other = BatchSet::factory()->create(['batch_id' => $set->batch_id, 'name' => 'Set B']);

    $this->withHeaders(spaHeaders())
        ->postJson('/api/v1/registrations', registrationPayload(['set_ids' => [$set->id, $other->id]]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('set_ids');
});

test('registration validates required fields', function () {
    $this->withHeaders(spaHeaders())
        ->postJson('/api/v1/registrations', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'mobile', 'password', 'first_name', 'last_name', 'location_id', 'set_ids', 'is_agreed']);
});

test('closed courses cannot be enrolled in', function () {
    $set = makeSet(course: ['status' => 'closed']);

    $this->withHeaders(spaHeaders())
        ->postJson('/api/v1/registrations', registrationPayload(['set_ids' => [$set->id]]))
        ->assertUnprocessable();
});

test('the expire command releases unpaid reservations', function () {
    $stale = Enrollment::factory()->pending()->create(['expires_at' => now()->subHour()]);
    $fresh = Enrollment::factory()->pending()->create();

    $this->artisan('enrollments:expire-pending')->assertSuccessful();

    expect($stale->refresh()->status)->toBe(EnrollmentStatus::EXPIRED)
        ->and($fresh->refresh()->status)->toBe(EnrollmentStatus::PENDING_PAYMENT);
});
