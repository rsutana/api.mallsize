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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('fee');
            $table->unsignedInteger('min_down_payment');
            $table->string('status', 30)->default('open')->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('status')->references('code')->on('course_statuses')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status', 30)->default('open');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('status')->references('code')->on('batch_statuses')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('batch_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('slots');
            $table->date('start_date')->index();
            $table->unsignedSmallInteger('training_days');
            $table->string('time_label')->default('8 AM – 5 PM');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_sets');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('courses');
    }
};
