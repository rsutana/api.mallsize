<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 30);
            $table->string('summary', 500)->nullable();
            $table->longText('body')->nullable();
            $table->string('cover_path')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->date('published_on')->nullable()->index();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->foreign('category')->references('code')->on('news_categories')->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('status')->references('code')->on('post_statuses')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('orientations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->text('details')->nullable();
            $table->date('date')->index();
            $table->string('time_label')->nullable();
            $table->string('format', 30)->default('on_site');
            $table->string('venue')->nullable();
            $table->boolean('is_social_linked')->default(false);
            $table->string('social_platform', 30)->nullable();
            $table->string('social_url', 500)->nullable();
            $table->string('link_mode', 30)->default('details');
            $table->string('status', 30)->default('draft')->index();
            $table->boolean('is_notify_students')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->foreign('format')->references('code')->on('orientation_formats')->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('social_platform')->references('code')->on('social_platforms')->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('link_mode')->references('code')->on('orientation_link_modes')->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('status')->references('code')->on('post_statuses')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('gallery_photos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('tag', 30)->index();
            $table->string('image_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('tag')->references('code')->on('gallery_tags')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->text('quote');
            $table->string('student_name');
            $table->string('course_label');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile', 20)->nullable();
            $table->string('email');
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('channels');
            $table->unsignedInteger('recipients_count')->default(1);
            $table->timestamp('sent_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('gallery_photos');
        Schema::dropIfExists('orientations');
        Schema::dropIfExists('news_posts');
    }
};
