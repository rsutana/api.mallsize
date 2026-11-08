<?php

use App\Models\Inquiry;
use App\Models\Location;
use App\Models\NewsPost;
use App\Models\Orientation;
use App\Notifications\InquiryReceivedNotification;
use Illuminate\Support\Facades\Notification;

test('public course list shows only open courses with slot counts', function () {
    $open = makeSet(['slots' => 20]);
    makeSet(course: ['status' => 'closed']);

    $this->getJson('/api/v1/courses')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $open->batch->course_id)
        ->assertJsonPath('data.0.batches.0.sets.0.slots_left', 20);
});

test('only active locations are listed and staff notes stay private', function () {
    Location::factory()->create(['notes' => 'secret']);
    Location::factory()->inactive()->create();

    $this->getJson('/api/v1/locations')->assertOk()->assertJsonCount(1, 'data')->assertJsonMissingPath('data.0.notes');
});

test('news hides drafts and future posts', function () {
    NewsPost::factory()->create(['title' => 'Visible']);
    NewsPost::factory()->draft()->create();
    NewsPost::factory()->create(['status' => 'scheduled', 'published_on' => now()->addWeek()]);

    $this->getJson('/api/v1/news')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Visible');
    $this->getJson('/api/v1/news/'.NewsPost::query()->where('status', 'draft')->value('slug'))->assertNotFound();
});

test('orientations list upcoming published ones', function () {
    Orientation::factory()->create();
    Orientation::factory()->draft()->create();
    Orientation::factory()->create(['date' => now()->subDay()]);

    $this->getJson('/api/v1/orientations')->assertOk()->assertJsonCount(1, 'data');
});

test('lookups and payment config are public', function () {
    $this->getJson('/api/v1/lookups')->assertOk()->assertJsonPath('data.location_types.0.code', 'pork');
    $this->getJson('/api/v1/payment-config')->assertOk()->assertJsonPath('data.driver', 'fake')->assertJsonCount(5, 'data.methods');
});

test('inquiries are stored and forwarded to staff', function () {
    Notification::fake();

    $this->postJson('/api/v1/inquiries', ['name' => 'Juan', 'email' => 'juan@email.com', 'message' => 'Schedules?'])->assertCreated();

    expect(Inquiry::query()->count())->toBe(1);
    Notification::assertSentOnDemand(InquiryReceivedNotification::class);
});
