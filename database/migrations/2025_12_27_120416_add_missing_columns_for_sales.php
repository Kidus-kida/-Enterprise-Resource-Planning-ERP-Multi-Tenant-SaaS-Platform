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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'min_sell_price')) {
                $table->decimal('min_sell_price', 22, 4)->default(0)->after('image');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'weight_excess_loss_applicable')) {
                $table->boolean('weight_excess_loss_applicable')->default(0)->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('min_sell_price');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('weight_excess_loss_applicable');
        });
    }
};
