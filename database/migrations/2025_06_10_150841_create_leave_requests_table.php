<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('leave_type_id');
            $table->date('leave_start_date')->nullable();
            $table->date('leave_end_date')->nullable();
            $table->longText('request_reason')->nullable();
            $table->json('attachements')->nullable();
            $table->string('half_day')->nullable();
            $table->integer('multiple_day')->default(0);
            $table->longText('reject_reason')->nullable();
            $table->integer('attended_by')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
