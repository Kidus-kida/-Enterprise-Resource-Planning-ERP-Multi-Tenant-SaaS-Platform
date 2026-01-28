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
        Schema::create('attendance_policy_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_timestamp_id')->constrained('attendance_timestamps')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('event_type'); // clock_in, clock_out
            $table->string('policy_type'); // late_arrival, early_departure, location_violation, etc.
            $table->string('status')->default('pending'); // pending, applied, waived, ignored
            $table->boolean('is_violation')->default(false);
            $table->decimal('penalty_amount', 10, 2)->default(0);
            $table->text('message')->nullable();
            $table->json('metadata')->nullable(); // Store additional context
            $table->timestamp('evaluated_at');
            $table->foreignId('evaluated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['attendance_timestamp_id', 'policy_type']);
            $table->index(['user_id', 'created_at']);
            $table->index(['policy_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_policy_events');
    }
};
