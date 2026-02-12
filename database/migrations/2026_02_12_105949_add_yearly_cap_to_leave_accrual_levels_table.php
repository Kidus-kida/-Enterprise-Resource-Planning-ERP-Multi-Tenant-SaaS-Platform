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
            $table->decimal('yearly_cap', 10, 4)->nullable()->after('accrual_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_accrual_levels', function (Blueprint $table) {
            $table->dropColumn('yearly_cap');
        });
    }
};
