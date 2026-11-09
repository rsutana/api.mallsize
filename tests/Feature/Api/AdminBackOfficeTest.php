<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lookups\CourseStatus;
use App\Models\Lookups\EnrollmentStatus;
use App\Models\Lookups\PaymentStatus;
use App\Models\NewsPost;
use App\Models\Orientation;
use App\Models\Payment;
use App\Notifications\BalanceReminderNotification;
use App\Notifications\OrientationPublishedNotification;
use App\Notifications\PaymentReviewedNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Notification::fake());

function paidEnrollment(int $paid = 500_000): Enrollment
{
    $enrollment = Enrollment::factory()->create();
    $payment = Payment::factory()->create(['student_id' => $enrollment->student_id, 'amount' => $paid]);
    $payment->allocations()->create(['enrollment_id' => $enrollment->id, 'amount' => $paid, 'amount_type' => 'down_payment']);

    return $enrollment;
}

test('students cannot reach the back office', function () {
    $this->actingAs(makeStudent()->user)->getJson('/api/v1/admin/overview')->assertForbidden();
});

test('staff cannot manage courses or locations', function () {
    $staff = staffUser();

    $this->actingAs($staff)->getJson('/api/v1/admin/overview')->assertOk();
    $this->actingAs($staff)->getJson('/api/v1/admin/courses')->assertForbidden();
    $this->actingAs($staff)->getJson('/api/v1/admin/locations')->assertForbidden();
});

test('overview reports KPIs', function () {
    paidEnrollment();

    $this->actingAs(staffUser())->getJson('/api/v1/admin/overview')
        ->assertOk()
        ->assertJsonPath('data.payments_to_verify', 1)
        ->assertJsonPath('data.enrolled_count', 1)
        ->assertJsonPath('data.collected', 500_000)
        ->assertJsonPath('data.outstanding', 1_750_000)
        ->assertJsonCount(1, 'data.due_soon');
});

test('overview reports seat usage per course, batch and set', function () {
    $enrollment = paidEnrollment();
    Enrollment::factory()->pending()->create(['batch_set_id' => $enrollment->batch_set_id]);
    Enrollment::factory()->create(['batch_set_id' => $enrollment->batch_set_id, 'status' => EnrollmentStatus::EXPIRED]);
    Course::factory()->create(['status' => CourseStatus::ARCHIVED]);

    $seats = ['slots' => 20, 'enrolled_count' => 1, 'pending_count' => 1, 'slots_taken' => 2, 'slots_left' => 18];

    $this->actingAs(staffUser())->getJson('/api/v1/admin/overview')
        ->assertOk()
        ->assertJsonCount(1, 'data.courses')
        ->assertJsonPath('data.courses.0.id', $enrollment->course_id)
        ->assertJson(['data' => ['courses' => [[
            ...$seats,
            'batches' => [[...$seats, 'sets' => [['id' => $enrollment->batch_set_id, ...$seats]]]],
        ]]]]);
});

test('staff can verify and reject payments', function () {
    $enrollment = paidEnrollment();
    $payment = Payment::query()->firstOrFail();
    $staff = staffUser();

    $this->actingAs($staff)->getJson('/api/v1/admin/payments')->assertOk()->assertJsonCount(1, 'data');
    $this->actingAs($staff)->postJson("/api/v1/admin/payments/{$payment->id}/verify")
        ->assertOk()->assertJsonPath('data.status', PaymentStatus::VERIFIED);
    $this->actingAs($staff)->postJson("/api/v1/admin/payments/{$payment->id}/verify")->assertUnprocessable();

    $other = paidEnrollment();
    $otherPayment = Payment::query()->latest('id')->firstOrFail();
    $this->actingAs($staff)->postJson("/api/v1/admin/payments/{$otherPayment->id}/reject", ['reason' => 'Reference not found'])
        ->assertOk()->assertJsonPath('data.status', PaymentStatus::REJECTED);

    expect($other->refresh()->amountPaid())->toBe(0)
        ->and($enrollment->refresh()->amountPaid())->toBe(500_000);
    Notification::assertSentTimes(PaymentReviewedNotification::class, 2);
});

test('enrolled students can be filtered and exported', function () {
    $dp = paidEnrollment();
    $full = paidEnrollment(2_250_000);
    $staff = staffUser();

    $this->actingAs($staff)->getJson('/api/v1/admin/enrollments')->assertOk()->assertJsonCount(2, 'data');
    $this->actingAs($staff)->getJson('/api/v1/admin/enrollments?payment_status=fully_paid')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $full->id);
    $this->actingAs($staff)->getJson('/api/v1/admin/enrollments?q='.$dp->student->reference_no)
        ->assertOk()->assertJsonCount(1, 'data');

    $csv = $this->actingAs($staff)->get('/api/v1/admin/enrollments/export?payment_status=down_payment_only')->assertOk()->streamedContent();
    expect($csv)->toContain('Reference,Name,Email')->toContain($dp->student->reference_no)->not->toContain($full->student->reference_no);
});

test('enrolled students are paginated 25 per page in a stable order', function () {
    Enrollment::factory()->count(27)->create(['enrolled_at' => now()->startOfMinute()]);
    $staff = staffUser();

    $first = $this->actingAs($staff)->getJson('/api/v1/admin/enrollments')
        ->assertOk()
        ->assertJsonCount(25, 'data')
        ->assertJsonPath('meta.per_page', 25)
        ->assertJsonPath('meta.total', 27)
        ->assertJsonPath('meta.last_page', 2);
    $second = $this->actingAs($staff)->getJson('/api/v1/admin/enrollments?page=2')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $ids = [...$first->json('data.*.id'), ...$second->json('data.*.id')];
    expect(array_unique($ids))->toHaveCount(27);
});

test('news posts and orientations are paginated 15 per page', function () {
    NewsPost::factory()->count(16)->create();
    Orientation::factory()->count(16)->create();
    $staff = staffUser();

    foreach (['news-posts', 'orientations'] as $resource) {
        $this->actingAs($staff)->getJson("/api/v1/admin/{$resource}")
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.total', 16);
        $this->actingAs($staff)->getJson("/api/v1/admin/{$resource}?page=2")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
});

test('balance reminders go to students with a balance', function () {
    $enrollment = paidEnrollment();
    paidEnrollment(2_250_000);

    $this->actingAs(staffUser())->postJson('/api/v1/admin/balance-reminders')
        ->assertOk()->assertJsonPath('data.students_reminded', 1);

    Notification::assertSentTo($enrollment->student->user, BalanceReminderNotification::class);
});

test('admins can create a course with batches and sets', function () {
    $payload = [
        'name' => 'Meat Processing – Goat', 'fee' => 1_800_000, 'min_down_payment' => 400_000, 'status' => 'open',
        'batches' => [['name' => 'Batch 1 — January 2027', 'status' => 'open', 'sets' => [
            ['name' => 'Set A', 'slots' => 20, 'start_date' => '2027-01-12', 'training_days' => 7, 'time_label' => '8 AM – 5 PM'],
            ['name' => 'Set B', 'slots' => 10, 'start_date' => '2027-01-26', 'training_days' => 7],
        ]]],
    ];

    $this->actingAs(adminUser())->postJson('/api/v1/admin/courses', $payload)
        ->assertCreated()
        ->assertJsonPath('data.slug', 'meat-processing-goat')
        ->assertJsonPath('data.total_slots', 30)
        ->assertJsonCount(2, 'data.batches.0.sets');
});

test('down payment cannot exceed the fee', function () {
    $this->actingAs(adminUser())->postJson('/api/v1/admin/courses', ['name' => 'X', 'fee' => 1000, 'min_down_payment' => 2000, 'status' => 'open', 'batches' => []])
        ->assertUnprocessable()->assertJsonValidationErrors(['min_down_payment', 'batches']);
});

test('sets and courses with students cannot be removed', function () {
    $enrollment = Enrollment::factory()->create();
    $course = Course::query()->findOrFail($enrollment->course_id);
    $admin = adminUser();

    $this->actingAs($admin)->putJson("/api/v1/admin/courses/{$course->id}", [
        'name' => $course->name, 'fee' => $course->fee, 'min_down_payment' => $course->min_down_payment, 'status' => 'open',
        'batches' => [['name' => 'New batch', 'status' => 'open', 'sets' => [['name' => 'Set A', 'slots' => 5, 'start_date' => '2027-05-01', 'training_days' => 5]]]],
    ])->assertUnprocessable()->assertJsonValidationErrors('course');

    $this->actingAs($admin)->deleteJson("/api/v1/admin/courses/{$course->id}")->assertUnprocessable();
    $this->actingAs($admin)->deleteJson('/api/v1/admin/courses/'.Course::factory()->create()->id)->assertNoContent();
});

test('admins manage partner locations', function () {
    $admin = adminUser();

    $id = $this->actingAs($admin)->postJson('/api/v1/admin/locations', [
        'name' => 'Santos Meat Shop', 'type' => 'pork_and_beef', 'contact_person' => 'Ramon Santos', 'mobile' => '0917 100 0001',
        'city' => 'Davao City', 'map_url' => 'http://insecure.example', 'is_active' => true,
    ])->assertUnprocessable()->assertJsonValidationErrors('map_url')->json('data.id');

    $id = $this->actingAs($admin)->postJson('/api/v1/admin/locations', [
        'name' => 'Santos Meat Shop', 'type' => 'pork_and_beef', 'contact_person' => 'Ramon Santos', 'mobile' => '0917 100 0001',
        'city' => 'Davao City', 'is_active' => true,
    ])->assertCreated()->json('data.id');

    $this->actingAs($admin)->patchJson("/api/v1/admin/locations/{$id}", [
        'name' => 'Santos Meat Shop', 'type' => 'pork', 'contact_person' => 'Ramon Santos', 'mobile' => '0917 100 0001',
        'city' => 'Davao City', 'is_active' => false,
    ])->assertOk()->assertJsonPath('data.type_label', 'Pork');

    $this->actingAs($admin)->deleteJson("/api/v1/admin/locations/{$id}")->assertNoContent();
});

test('staff publish news with a cover photo', function () {
    Storage::fake('public');

    $id = $this->actingAs(staffUser())->post('/api/v1/admin/news-posts', [
        'title' => 'Enrollment now open', 'category' => 'enrollment', 'summary' => 'Reserve your slot.',
        'status' => 'published', 'published_on' => now()->toDateString(), 'is_featured' => '1',
        'cover' => UploadedFile::fake()->image('cover.jpg', 1200, 630),
    ], ['Accept' => 'application/json'])->assertCreated()->assertJsonPath('data.slug', 'enrollment-now-open')->json('data.id');

    Storage::disk('public')->assertExists(NewsPost::query()->findOrFail($id)->cover_path);
    $this->getJson('/api/v1/news/enrollment-now-open')->assertOk();
});

test('publishing an orientation with notify emails enrolled students once', function () {
    $enrollment = Enrollment::factory()->create();
    $staff = staffUser();
    $payload = [
        'title' => 'Pre-training orientation', 'course_id' => $enrollment->course_id, 'date' => now()->addWeek()->toDateString(),
        'time_label' => '9:00 AM', 'format' => 'on_site', 'venue' => 'Training room', 'is_social_linked' => true,
        'social_platform' => 'facebook', 'social_url' => 'https://www.tiktok.com/@x', 'link_mode' => 'direct',
        'status' => 'published', 'is_notify_students' => true,
    ];

    $this->actingAs($staff)->postJson('/api/v1/admin/orientations', $payload)
        ->assertUnprocessable()->assertJsonValidationErrors('social_url');

    $id = $this->actingAs($staff)->postJson('/api/v1/admin/orientations', [...$payload, 'social_url' => 'https://www.facebook.com/mallsize/posts/1'])
        ->assertCreated()->json('data.id');
    $this->actingAs($staff)->putJson("/api/v1/admin/orientations/{$id}", [...$payload, 'social_url' => 'https://www.facebook.com/mallsize/posts/1', 'title' => 'Updated'])
        ->assertOk();

    Notification::assertSentToTimes($enrollment->student->user, OrientationPublishedNotification::class, 1);
    expect(Orientation::query()->findOrFail($id)->notified_at)->not->toBeNull();
});
