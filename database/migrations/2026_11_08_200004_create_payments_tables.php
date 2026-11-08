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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('reference_no', 20)->unique();
            $table->unsignedInteger('amount');
            $table->string('method', 30);
            $table->string('status', 30)->default('pending')->index();
            $table->string('provider', 20);
            $table->string('provider_intent_id')->nullable()->index();
            $table->string('provider_payment_id')->nullable();
            $table->string('provider_reference')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rejected_reason')->nullable();
            $table->timestamps();

            $table->foreign('method')->references('code')->on('payment_methods')->cascadeOnUpdate()->restrictOnDelete();

            $table->foreign('status')->references('code')->on('payment_statuses')->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount');
            $table->string('amount_type', 30);
            $table->timestamps();

            $table->foreign('amount_type')->references('code')->on('payment_amount_types')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
    }
};
