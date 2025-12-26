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
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'is_vat')) {
                $table->boolean('is_vat')->default(0)->after('invoice_no');
            }
            if (!Schema::hasColumn('transactions', 'invoice_date')) {
                $table->dateTime('invoice_date')->nullable()->after('is_vat');
            }
            if (!Schema::hasColumn('transactions', 'store_id')) {
                $table->unsignedBigInteger('store_id')->nullable()->index()->after('location_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['is_vat', 'invoice_date', 'store_id']);
        });
    }
};
