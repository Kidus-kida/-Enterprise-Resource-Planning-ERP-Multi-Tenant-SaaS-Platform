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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->default(1);
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('type')->index()->comment('sell, purchase, opening_balance, payment, direct_customer_loan, etc');
            $table->string('status')->default('final');
            $table->string('payment_status')->nullable();

            $table->unsignedBigInteger('contact_id')->nullable()->index();
            //$table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');

            $table->string('invoice_no')->nullable();
            $table->string('ref_no')->nullable();
            $table->dateTime('transaction_date');

            $table->decimal('total_before_tax', 22, 4)->default(0);
            $table->decimal('tax_amount', 22, 4)->default(0);
            $table->decimal('discount_amount', 22, 4)->default(0);
            $table->string('discount_type')->nullable(); // fixed, percentage

            $table->decimal('final_total', 22, 4)->default(0);

            $table->unsignedBigInteger('created_by')->nullable();

            // Specific for loans
            $table->string('approved_user')->nullable();
            $table->text('transaction_note')->nullable();
            $table->boolean('is_settlement')->default(false);

            $table->text('shipping_details')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_status')->nullable();
            $table->string('delivered_to')->nullable();
            $table->decimal('shipping_charges', 22, 4)->default(0);
            $table->text('additional_notes')->nullable();
            $table->text('staff_note')->nullable();
            $table->integer('pay_term_number')->nullable();
            $table->string('pay_term_type')->nullable();
            $table->unsignedBigInteger('selling_price_group_id')->nullable();
            $table->string('order_status')->nullable();
            $table->string('order_no')->nullable();
            $table->dateTime('order_date')->nullable();
            $table->boolean('is_duplicate')->default(0);
            $table->boolean('is_suspend')->default(0);
            $table->boolean('is_recurring')->default(0);
            $table->double('recur_interval', 22, 4)->nullable();
            $table->string('recur_interval_type')->nullable();
            $table->integer('recur_repetitions')->default(0);
            $table->string('subscription_no')->nullable();
            $table->unsignedBigInteger('types_of_service_id')->nullable();
            $table->decimal('packing_charge', 22, 4)->nullable();
            $table->string('packing_charge_type')->nullable();
            $table->text('service_custom_field_1')->nullable();
            $table->text('service_custom_field_2')->nullable();
            $table->text('service_custom_field_3')->nullable();
            $table->text('service_custom_field_4')->nullable();
            $table->boolean('is_created_from_api')->default(0);
            $table->unsignedBigInteger('repair_job_sheet_id')->nullable();
            $table->boolean('is_credit_sale')->default(0);
            $table->decimal('rp_redeemed', 22, 4)->default(0)->comment('Reward points redeemed');
            $table->decimal('rp_earned', 22, 4)->default(0)->comment('Reward points earned');
            $table->decimal('rp_redeemed_amount', 22, 4)->default(0)->comment('Reward points redeemed amount');
            $table->string('customer_ref')->nullable();
            $table->string('sub_type')->nullable();
            $table->text('order_addresses')->nullable();
            $table->boolean('is_customer_order')->default(0);

            $table->boolean('is_pos')->default(0);
            $table->integer('reprint_no')->default(0);


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
