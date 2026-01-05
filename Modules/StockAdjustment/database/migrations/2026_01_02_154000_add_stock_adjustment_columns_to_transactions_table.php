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
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'adjustment_type')) {
                $table->string('adjustment_type')->nullable()->after('type');
            }
            if (!Schema::hasColumn('transactions', 'stock_adjustment_type')) {
                $table->string('stock_adjustment_type')->nullable()->after('adjustment_type');
            }
            if (!Schema::hasColumn('transactions', 'total_amount_recovered')) {
                $table->decimal('total_amount_recovered', 22, 4)->default(0)->after('final_total');
            }
            if (!Schema::hasColumn('transactions', 'additional_notes')) {
                $table->text('additional_notes')->nullable()->after('total_amount_recovered');
            }
            if (!Schema::hasColumn('transactions', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable()->after('location_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['adjustment_type', 'stock_adjustment_type', 'total_amount_recovered', 'additional_notes', 'store_id']);
        });
    }
};
