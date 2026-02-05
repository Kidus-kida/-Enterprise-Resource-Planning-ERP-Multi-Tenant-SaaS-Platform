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
        Schema::table('leave_types', function (Blueprint $table) {
            $table->boolean('can_carry_forward')->default(false)->after('max_negative_balance');
            $table->integer('max_carry_forward')->default(0)->after('can_carry_forward'); // Max days to carry over
            $table->integer('carry_forward_expiry')->nullable()->after('max_carry_forward'); // Months until expiry (e.g. 3 = March 31st)
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['can_carry_forward', 'max_carry_forward', 'carry_forward_expiry']);
        });
    }
};
