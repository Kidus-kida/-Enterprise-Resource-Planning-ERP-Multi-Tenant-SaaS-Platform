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
        Schema::table('leave_accrual_levels', function (Blueprint $table) {
            $table->enum('accrual_unit', ['days', 'hours'])->default('days')->after('accrual_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_accrual_levels', function (Blueprint $table) {
            $table->dropColumn('accrual_unit');
        });
    }
};
