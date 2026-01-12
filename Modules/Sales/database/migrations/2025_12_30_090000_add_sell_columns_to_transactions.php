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
            // Duplicate invoice tracking
            if (!Schema::hasColumn('transactions', 'is_duplicate')) {
                $table->boolean('is_duplicate')->default(0)->after('location_id');
            }
            
            // Customer and pricing
            if (!Schema::hasColumn('transactions', 'customer_group_id')) {
                $table->unsignedBigInteger('customer_group_id')->nullable()->after('contact_id');
            }
            
            if (!Schema::hasColumn('transactions', 'selling_price_group_id')) {
                $table->unsignedBigInteger('selling_price_group_id')->nullable()->after('discount_type');
            }
            
            // Tax reference
            if (!Schema::hasColumn('transactions', 'tax_id')) {
                $table->unsignedBigInteger('tax_id')->nullable()->after('transaction_date');
            }
            
            // Notes
            if (!Schema::hasColumn('transactions', 'additional_notes')) {
                $table->text('additional_notes')->nullable()->after('final_total');
            }
            
            if (!Schema::hasColumn('transactions', 'staff_note')) {
                $table->text('staff_note')->nullable()->after('additional_notes');
            }
            
            // Sale flags
            if (!Schema::hasColumn('transactions', 'is_direct_sale')) {
                $table->boolean('is_direct_sale')->default(0)->after('created_by');
            }
            
            if (!Schema::hasColumn('transactions', 'is_quotation')) {
                $table->boolean('is_quotation')->default(0)->after('is_direct_sale');
            }
            
            if (!Schema::hasColumn('transactions', 'is_customer_order')) {
                $table->boolean('is_customer_order')->default(0)->after('is_quotation');
            }
            
            if (!Schema::hasColumn('transactions', 'is_suspend')) {
                $table->boolean('is_suspend')->default(0)->after('is_customer_order');
            }
            
            // Commission
            if (!Schema::hasColumn('transactions', 'commission_agent')) {
                $table->unsignedBigInteger('commission_agent')->nullable()->after('is_suspend');
            }
            
            // Shipping
            if (!Schema::hasColumn('transactions', 'shipping_details')) {
                $table->string('shipping_details')->nullable()->after('commission_agent');
            }
            
            if (!Schema::hasColumn('transactions', 'shipping_address')) {
                $table->text('shipping_address')->nullable()->after('shipping_details');
            }
            
            if (!Schema::hasColumn('transactions', 'shipping_status')) {
                $table->string('shipping_status')->nullable()->after('shipping_address');
            }
            
            if (!Schema::hasColumn('transactions', 'delivered_to')) {
                $table->string('delivered_to')->nullable()->after('shipping_status');
            }
            
            if (!Schema::hasColumn('transactions', 'shipping_charges')) {
                $table->decimal('shipping_charges', 22, 4)->default(0)->after('delivered_to');
            }
            
            // Currency
            if (!Schema::hasColumn('transactions', 'exchange_rate')) {
                $table->decimal('exchange_rate', 20, 3)->default(1)->after('shipping_charges');
            }
            
            // Payment terms
            if (!Schema::hasColumn('transactions', 'pay_term_number')) {
                $table->integer('pay_term_number')->nullable()->after('exchange_rate');
            }
            
            if (!Schema::hasColumn('transactions', 'pay_term_type')) {
                $table->string('pay_term_type')->nullable()->after('pay_term_number');
            }
            
            // Recurring transactions
            if (!Schema::hasColumn('transactions', 'is_recurring')) {
                $table->boolean('is_recurring')->default(0)->after('pay_term_type');
            }
            
            if (!Schema::hasColumn('transactions', 'recur_interval')) {
                $table->integer('recur_interval')->nullable()->after('is_recurring');
            }
            
            if (!Schema::hasColumn('transactions', 'recur_interval_type')) {
                $table->string('recur_interval_type')->nullable()->after('recur_interval');
            }
            
            if (!Schema::hasColumn('transactions', 'subscription_no')) {
                $table->string('subscription_no')->nullable()->after('recur_interval_type');
            }
            
            if (!Schema::hasColumn('transactions', 'recur_repetitions')) {
                $table->integer('recur_repetitions')->default(0)->after('subscription_no');
            }
            
            // Order details
            if (!Schema::hasColumn('transactions', 'order_addresses')) {
                $table->text('order_addresses')->nullable()->after('recur_repetitions');
            }
            
            if (!Schema::hasColumn('transactions', 'sub_type')) {
                $table->string('sub_type')->nullable()->after('order_addresses');
            }
            
            // Reward points
            if (!Schema::hasColumn('transactions', 'rp_earned')) {
                $table->integer('rp_earned')->default(0)->after('sub_type')->comment('Reward points earned');
            }
            
            if (!Schema::hasColumn('transactions', 'rp_redeemed')) {
                $table->integer('rp_redeemed')->default(0)->after('rp_earned')->comment('Reward points redeemed');
            }
            
            if (!Schema::hasColumn('transactions', 'rp_redeemed_amount')) {
                $table->decimal('rp_redeemed_amount', 22, 4)->default(0)->after('rp_redeemed');
            }
            
            // API flag
            if (!Schema::hasColumn('transactions', 'is_created_from_api')) {
                $table->boolean('is_created_from_api')->default(0)->after('rp_redeemed_amount');
            }
            
            // Service type
            if (!Schema::hasColumn('transactions', 'types_of_service_id')) {
                $table->unsignedBigInteger('types_of_service_id')->nullable()->after('is_created_from_api');
            }
            
            // Packing charges
            if (!Schema::hasColumn('transactions', 'packing_charge')) {
                $table->decimal('packing_charge', 22, 4)->default(0)->after('types_of_service_id');
            }
            
            if (!Schema::hasColumn('transactions', 'packing_charge_type')) {
                $table->string('packing_charge_type')->nullable()->after('packing_charge');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $columns = [
                'is_duplicate',
                'customer_group_id',
                'selling_price_group_id',
                'tax_id',
                'additional_notes',
                'staff_note',
                'is_direct_sale',
                'is_quotation',
                'is_customer_order',
                'is_suspend',
                'commission_agent',
                'shipping_details',
                'shipping_address',
                'shipping_status',
                'delivered_to',
                'shipping_charges',
                'exchange_rate',
                'pay_term_number',
                'pay_term_type',
                'is_recurring',
                'recur_interval',
                'recur_interval_type',
                'subscription_no',
                'recur_repetitions',
                'order_addresses',
                'sub_type',
                'rp_earned',
                'rp_redeemed',
                'rp_redeemed_amount',
                'is_created_from_api',
                'types_of_service_id',
                'packing_charge',
                'packing_charge_type',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
