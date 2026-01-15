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
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('price_per_user', 10, 2)->nullable()->after('price')
                ->comment('Price per additional user beyond minimum');
            $table->integer('min_users')->default(1)->after('user_count')
                ->comment('Minimum users included in base price');
            $table->boolean('is_per_user_pricing')->default(false)->after('min_users')
                ->comment('Enable dynamic per-user pricing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['price_per_user', 'min_users', 'is_per_user_pricing']);
        });
    }
};
