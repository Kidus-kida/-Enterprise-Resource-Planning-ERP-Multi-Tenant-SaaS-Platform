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
        Schema::table('leave_allocations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved')->after('allocation_type');
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->boolean('allow_negative_balance')->default(false)->after('auto_approve_if_balance');
            $table->integer('max_negative_balance')->default(0)->after('allow_negative_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_allocations', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['allow_negative_balance', 'max_negative_balance']);
        });
    }
};
