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
        Schema::table('shipments', function (Blueprint $table) {
            $table->text('shipping_details')->nullable()->after('transaction_id');
            $table->text('shipping_address')->nullable()->after('shipping_details');
            $table->string('shipping_status')->nullable()->after('shipping_address');
            $table->string('delivered_to')->nullable()->after('shipping_status');
            $table->decimal('shipping_charges', 22, 4)->default(0)->after('delivered_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['shipping_details', 'shipping_address', 'shipping_status', 'delivered_to', 'shipping_charges']);
        });
    }
};
