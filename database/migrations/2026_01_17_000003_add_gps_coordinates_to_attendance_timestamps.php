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
        Schema::table('attendance_timestamps', function (Blueprint $table) {
            // Add columns to store raw GPS coordinates
            $table->decimal('latitude', 10, 8)->nullable()->after('location');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->decimal('co_latitude', 10, 8)->nullable()->after('co_location');
            $table->decimal('co_longitude', 11, 8)->nullable()->after('co_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_timestamps', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'co_latitude', 'co_longitude']);
        });
    }
};
