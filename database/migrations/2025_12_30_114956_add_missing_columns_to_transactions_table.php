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
            // Shipping
            if (!Schema::hasColumn('transactions', 'shipping_details')) {
                $table->text('shipping_details')->nullable()->after('status');
            }
            if (!Schema::hasColumn('transactions', 'shipping_address')) {
                $table->text('shipping_address')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'shipping_status')) {
                $table->string('shipping_status')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'delivered_to')) {
                $table->string('delivered_to')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'shipping_charges')) {
                $table->decimal('shipping_charges', 22, 4)->default(0);
            }

            // Notes
            if (!Schema::hasColumn('transactions', 'additional_notes')) {
                $table->text('additional_notes')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'staff_note')) {
                $table->text('staff_note')->nullable();
            }

            // Payment Terms
             if (!Schema::hasColumn('transactions', 'pay_term_number')) {
                $table->integer('pay_term_number')->nullable();
            }
             if (!Schema::hasColumn('transactions', 'pay_term_type')) {
                $table->string('pay_term_type')->nullable();
            }

            // Pricing & Orders
            if (!Schema::hasColumn('transactions', 'selling_price_group_id')) {
                 $table->unsignedBigInteger('selling_price_group_id')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'order_status')) {
                 $table->string('order_status')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'order_no')) {
                 $table->string('order_no')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'order_date')) {
                 $table->dateTime('order_date')->nullable();
            }

            // Duplication & Recursion
             if (!Schema::hasColumn('transactions', 'is_duplicate')) {
                $table->boolean('is_duplicate')->default(0);
             }
             if (!Schema::hasColumn('transactions', 'is_suspend')) {
                $table->boolean('is_suspend')->default(0);
             }
             if (!Schema::hasColumn('transactions', 'is_recurring')) {
                $table->boolean('is_recurring')->default(0);
             }
             if (!Schema::hasColumn('transactions', 'recur_interval')) {
                $table->double('recur_interval', 22, 4)->nullable();
             }
             if (!Schema::hasColumn('transactions', 'recur_interval_type')) {
                $table->string('recur_interval_type')->nullable();
             }
             if (!Schema::hasColumn('transactions', 'recur_repetitions')) {
                $table->integer('recur_repetitions')->default(0);
             }
             if (!Schema::hasColumn('transactions', 'subscription_no')) {
                $table->string('subscription_no')->nullable();
             }

            // Service & API
             if (!Schema::hasColumn('transactions', 'types_of_service_id')) {
                $table->unsignedBigInteger('types_of_service_id')->nullable();
             }
             if (!Schema::hasColumn('transactions', 'packing_charge')) {
                $table->decimal('packing_charge', 22, 4)->nullable();
             }
             if (!Schema::hasColumn('transactions', 'packing_charge_type')) {
                $table->string('packing_charge_type')->nullable();
             }
             if (!Schema::hasColumn('transactions', 'service_custom_field_1')) {
                $table->text('service_custom_field_1')->nullable();
             }
             if (!Schema::hasColumn('transactions', 'service_custom_field_2')) {
                $table->text('service_custom_field_2')->nullable();
             }
             if (!Schema::hasColumn('transactions', 'service_custom_field_3')) {
                $table->text('service_custom_field_3')->nullable();
             }
             if (!Schema::hasColumn('transactions', 'service_custom_field_4')) {
                $table->text('service_custom_field_4')->nullable();
             }
             if (!Schema::hasColumn('transactions', 'is_created_from_api')) {
                $table->boolean('is_created_from_api')->default(0);
            }

            // Other
             if (!Schema::hasColumn('transactions', 'repair_job_sheet_id')) {
                $table->unsignedBigInteger('repair_job_sheet_id')->nullable();
             }
             if (!Schema::hasColumn('transactions', 'is_credit_sale')) {
                $table->boolean('is_credit_sale')->default(0);
             }
             if (!Schema::hasColumn('transactions', 'rp_earned')) {
                $table->decimal('rp_earned', 22, 4)->default(0)->comment('Reward points earned');
             }
             if (!Schema::hasColumn('transactions', 'rp_redeemed')) {
                $table->decimal('rp_redeemed', 22, 4)->default(0)->comment('Reward points redeemed');
             }
             if (!Schema::hasColumn('transactions', 'rp_redeemed_amount')) {
                $table->decimal('rp_redeemed_amount', 22, 4)->default(0)->comment('Reward points redeemed amount');
             }
             if (!Schema::hasColumn('transactions', 'customer_ref')) {
                $table->string('customer_ref')->nullable();
             }
             if (!Schema::hasColumn('transactions', 'sub_type')) {
                $table->string('sub_type')->nullable();
             }
             if (!Schema::hasColumn('transactions', 'order_addresses')) {
                $table->text('order_addresses')->nullable();
             }
             if (!Schema::hasColumn('transactions', 'is_customer_order')) {
                $table->boolean('is_customer_order')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
             $table->dropColumn([
                'shipping_details', 'shipping_address', 'shipping_status', 'delivered_to', 'shipping_charges',
                'additional_notes', 'staff_note',
                'pay_term_number', 'pay_term_type',
                'selling_price_group_id', 'order_status', 'order_no', 'order_date',
                'is_duplicate', 'is_suspend', 'is_recurring', 'recur_interval', 'recur_interval_type', 'recur_repetitions', 'subscription_no',
                'types_of_service_id', 'packing_charge', 'packing_charge_type', 'service_custom_field_1', 'service_custom_field_2', 'service_custom_field_3', 'service_custom_field_4',
                'is_created_from_api',
                'repair_job_sheet_id', 'is_credit_sale', 'rp_earned', 'rp_redeemed', 'rp_redeemed_amount',
                'customer_ref', 'sub_type', 'order_addresses', 'is_customer_order'
            ]);
        });
    }
};
