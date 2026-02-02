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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('type_name');
            $table->integer('max_date_allowed');
            $table->string('leave_allowed_interval')->nullable();
            $table->string('description')->nullable();
            $table->string('status')->default('allowed');
            $table->timestamps();
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('leave_type_id');
            $table->date('leave_start_date')->nullable();
            $table->date('leave_end_date')->nullable();
            $table->string('request_reason')->nullable();
            $table->longText('attachements')->nullable();
            $table->string('half_day')->nullable();
            $table->integer('multiple_day')->default(0);
            $table->string('reject_reason')->nullable();
            $table->integer('attended_by')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('anunal_leaves', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->decimal('current_year', 8, 2)->default(0.00);
            $table->decimal('previous_year', 8, 2)->default(0.00);
            $table->string('year_bpy')->default('2025');
            $table->decimal('per_month', 8, 2)->default(0.00);
            $table->integer('per_year')->default(0);
            $table->integer('total_anunal_leave')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anunal_leaves');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_types');
    }
};
