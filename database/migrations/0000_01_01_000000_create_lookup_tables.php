<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Lookup tables for every option list and status. Rows are referenced by
 * their string `code` (foreign keys cascade on update). Rows flagged
 * `is_system` are relied on by application logic and must not be removed.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->createLookup('user_roles', [
            ['student', 'Student', true],
            ['staff', 'Staff', true],
            ['admin', 'Admin', true],
        ]);
        $this->createLookup('course_statuses', [
            ['open', 'Open', true],
            ['closed', 'Closed', true],
            ['archived', 'Archived', true],
        ]);
        $this->createLookup('batch_statuses', [
            ['open', 'Open', true],
            ['full', 'Full', true],
            ['closed', 'Closed', true],
        ]);
        $this->createLookup('enrollment_statuses', [
            ['pending_payment', 'Pending payment', true],
            ['enrolled', 'Enrolled', true],
            ['cancelled', 'Cancelled', true],
            ['expired', 'Expired', true],
        ]);
        $this->createLookup('enrollment_payment_statuses', [
            ['fully_paid', 'Fully paid', true],
            ['down_payment_only', 'Down payment only', true],
            ['awaiting_payment', 'Awaiting payment', true],
        ]);
        $this->createLookup('payment_statuses', [
            ['pending', 'Pending', true],
            ['paid', 'Paid', true],
            ['verified', 'Verified', true],
            ['failed', 'Failed', true],
            ['rejected', 'Rejected', true],
        ]);
        $this->createLookup('payment_methods', [
            ['gcash', 'GCash', true],
            ['maya', 'Maya', true],
            ['qrph', 'QR Ph', true],
            ['shopeepay', 'ShopeePay', true],
            ['card', 'Debit/Credit card', true],
            ['bank_transfer', 'Bank transfer', false],
        ]);
        $this->createLookup('payment_amount_types', [
            ['down_payment', 'Down payment', true],
            ['full', 'Full payment', true],
            ['balance', 'Remaining balance', true],
            ['partial', 'Partial', true],
        ]);
        $this->createLookup('location_types', [
            ['pork', 'Pork', false],
            ['beef', 'Beef', false],
            ['pork_and_beef', 'Pork & beef', false],
            ['poultry', 'Poultry', false],
            ['processed_meats', 'Processed meats', false],
        ]);
        $this->createLookup('news_categories', [
            ['announcement', 'Announcement', false],
            ['enrollment', 'Enrollment', false],
            ['event', 'Event', false],
            ['graduation', 'Graduation', false],
        ]);
        $this->createLookup('post_statuses', [
            ['draft', 'Draft', true],
            ['scheduled', 'Scheduled', true],
            ['published', 'Published', true],
        ]);
        $this->createLookup('orientation_formats', [
            ['on_site', 'On-site', false],
            ['online', 'Online', false],
            ['facebook_live', 'Facebook Live', false],
        ]);
        $this->createLookup('social_platforms', [
            ['facebook', 'Facebook', false],
            ['instagram', 'Instagram', false],
            ['tiktok', 'TikTok', false],
            ['youtube', 'YouTube', false],
        ]);
        $this->createLookup('orientation_link_modes', [
            ['direct', 'Opens the social post directly', true],
            ['details', 'Shows details, with a link to the post', true],
        ]);
        $this->createLookup('sexes', [
            ['male', 'Male', false],
            ['female', 'Female', false],
        ]);
        $this->createLookup('education_levels', [
            ['elementary', 'Elementary', false],
            ['high_school', 'High school', false],
            ['senior_high_school', 'Senior high school', false],
            ['college', 'College', false],
            ['vocational', 'Vocational', false],
        ]);
        $this->createLookup('gallery_tags', [
            ['team', 'Team', false],
            ['training', 'Training', false],
            ['graduation', 'Graduation', false],
            ['facility', 'Facility', false],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_tags');
        Schema::dropIfExists('education_levels');
        Schema::dropIfExists('sexes');
        Schema::dropIfExists('orientation_link_modes');
        Schema::dropIfExists('social_platforms');
        Schema::dropIfExists('orientation_formats');
        Schema::dropIfExists('post_statuses');
        Schema::dropIfExists('news_categories');
        Schema::dropIfExists('location_types');
        Schema::dropIfExists('payment_amount_types');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('payment_statuses');
        Schema::dropIfExists('enrollment_payment_statuses');
        Schema::dropIfExists('enrollment_statuses');
        Schema::dropIfExists('batch_statuses');
        Schema::dropIfExists('course_statuses');
        Schema::dropIfExists('user_roles');
    }

    /**
     * @param  list<array{0: string, 1: string, 2: bool}>  $rows
     */
    private function createLookup(string $table, array $rows): void
    {
        Schema::create($table, function (Blueprint $table): void {
            $table->string('code', 30)->primary();
            $table->string('label');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        $now = now();

        DB::table($table)->insert(array_map(fn (array $row, int $i): array => [
            'code' => $row[0],
            'label' => $row[1],
            'sort_order' => $i + 1,
            'is_active' => true,
            'is_system' => $row[2],
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows, array_keys($rows)));
    }
};
