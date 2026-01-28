<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->boolean('popup_load_save_data')->default(0)->after('font_style');
            $table->boolean('day_end_enable')->default(0)->after('popup_load_save_data');
            $table->boolean('enable_line_discount')->default(0)->after('day_end_enable');
            $table->boolean('duplicate_orders_allowed')->default(0)->after('enable_line_discount');
            $table->boolean('show_for_customers')->default(0)->after('duplicate_orders_allowed');
            $table->text('business_categories')->nullable()->after('show_for_customers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'popup_load_save_data',
                'day_end_enable',
                'enable_line_discount',
                'duplicate_orders_allowed',
                'show_for_customers',
                'business_categories'
            ]);
        });
    }
};
