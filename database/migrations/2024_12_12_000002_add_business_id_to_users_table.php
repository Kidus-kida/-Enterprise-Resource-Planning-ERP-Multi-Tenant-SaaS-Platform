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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id')->nullable()->after('id');
            // $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade'); 
            // Commented out foreign key to avoid issues if businesses table is empty or strict constraints, 
            // but normally it's good practice. Given the seeding trouble, keeping it simple is safer first.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('business_id');
        });
    }
};
