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
        Schema::table('business', function (Blueprint $table) {
            $table->text('email_settings')->nullable();
            $table->text('sms_settings')->nullable();
            $table->text('common_settings')->nullable();
            $table->text('custom_labels')->nullable();
            $table->text('contact_fields')->nullable();
            $table->text('ref_no_starting_number')->nullable();
            $table->boolean('enable_rp')->default(0);
            $table->string('rp_name')->nullable();
            $table->decimal('amount_for_unit_rp', 22, 4)->default(1);
            $table->decimal('min_order_total_for_rp', 22, 4)->default(1);
            $table->integer('max_rp_per_order')->nullable();
            $table->decimal('redeem_amount_per_unit_rp', 22, 4)->default(1);
            $table->decimal('min_order_total_for_redeem', 22, 4)->default(1);
            $table->integer('min_redeem_point')->nullable();
            $table->integer('max_redeem_point')->nullable();
            $table->integer('rp_expiry_period')->nullable();
            $table->enum('rp_expiry_type', ['month', 'year'])->default('year');
             // Add any other missing columns if identified later
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business', function (Blueprint $table) {
            $table->dropColumn([
                'email_settings',
                'sms_settings',
                'common_settings',
                'custom_labels',
                'contact_fields',
                'ref_no_starting_number',
                'enable_rp',
                'rp_name',
                'amount_for_unit_rp',
                'min_order_total_for_rp',
                'max_rp_per_order',
                'redeem_amount_per_unit_rp',
                'min_order_total_for_redeem',
                'min_redeem_point',
                'max_redeem_point',
                'rp_expiry_period',
                'rp_expiry_type'
            ]);
        });
    }
};
