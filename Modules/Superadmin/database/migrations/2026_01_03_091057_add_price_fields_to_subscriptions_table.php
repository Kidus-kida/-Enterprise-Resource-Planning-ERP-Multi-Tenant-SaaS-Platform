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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('base_price', 22, 4)->default(0)->after('package_id');
            $table->decimal('addons_price', 22, 4)->default(0)->after('base_price');
            $table->decimal('total_price', 22, 4)->default(0)->after('addons_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['base_price', 'addons_price', 'total_price']);
        });
    }
};
