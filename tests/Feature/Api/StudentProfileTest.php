<?php

test('students can update their profile and notification settings', function () {
    $student = makeStudent();

    $this->actingAs($student->user)->patchJson('/api/v1/student/profile', [
        'first_name' => 'Juan', 'middle_name' => 'P', 'last_name' => 'Dela Cruz',
        'email' => 'juan.new@email.com', 'mobile' => '0917 555 0148', 'address' => 'Davao City',
        'sex' => 'male', 'education' => 'college',
        'is_email_notifications' => true, 'is_sms_notifications' => false,
    ])->assertOk()
        ->assertJsonPath('email', 'juan.new@email.com')
        ->assertJsonPath('is_sms_notifications', false)
        ->assertJsonPath('student.full_name', 'Juan P Dela Cruz');

    expect($student->user->refresh()->name)->toBe('Juan Dela Cruz');
});

test('profile email must stay unique', function () {
    $student = makeStudent();
    $other = makeStudent();

    $this->actingAs($student->user)->patchJson('/api/v1/student/profile', [
        'first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'email' => $other->user->email, 'mobile' => '0917 555 0148',
        'is_email_notifications' => true, 'is_sms_notifications' => true,
    ])->assertUnprocessable()->assertJsonValidationErrors('email');
});

test('staff have no student profile endpoint', function () {
    $this->actingAs(staffUser())->patchJson('/api/v1/student/profile', [])->assertForbidden();
});
