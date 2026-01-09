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
        if (Schema::hasTable('business_locations')) {
            Schema::table('business_locations', function (Blueprint $table) {
                if (!Schema::hasColumn('business_locations', 'is_active')) {
                    $table->boolean('is_active')->default(1)->after('email');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('business_locations')) {
            Schema::table('business_locations', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
