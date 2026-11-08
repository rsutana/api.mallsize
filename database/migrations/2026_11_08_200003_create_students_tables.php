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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('reference_no', 20)->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('birthdate')->nullable();
            $table->string('sex', 30)->nullable();
            $table->string('education', 30)->nullable();
            $table->string('address', 500)->nullable();
            $table->timestamps();

            $table->foreign('sex')->references('code')->on('sexes')->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('education')->references('code')->on('education_levels')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_set_id')->constrained()->restrictOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('fee');
            $table->unsignedInteger('min_down_payment');
            $table->string('status', 30)->default('pending_payment')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();

            $table->foreign('status')->references('code')->on('enrollment_statuses')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('students');
    }
};
