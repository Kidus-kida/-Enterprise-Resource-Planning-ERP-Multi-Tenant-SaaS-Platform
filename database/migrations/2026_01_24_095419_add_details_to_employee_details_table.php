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
        Schema::table('employee_details', function (Blueprint $table) {
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('job_position_id')->nullable()->constrained('job_positions')->onDelete('set null');
            $table->string('job_title')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['company_id']);
            $table->dropForeign(['job_position_id']);
            $table->dropColumn(['manager_id', 'company_id', 'job_position_id', 'job_title']);
        });
    }
};
