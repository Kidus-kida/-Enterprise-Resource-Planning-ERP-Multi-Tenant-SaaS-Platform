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
