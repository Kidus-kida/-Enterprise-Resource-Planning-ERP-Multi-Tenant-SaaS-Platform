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
        Schema::create('transaction_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->nullable()->index();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->boolean('is_return')->default(false)->comment('Used during sales to return change');
            $table->decimal('amount', 22, 4)->default(0);
            $table->string('method')->nullable()->comment('cash, card, cheque, bank_transfer, custom_pay_1, custom_pay_2, custom_pay_3, etc');
            $table->string('transaction_no')->nullable();
            $table->string('card_transaction_number')->nullable();
            $table->string('card_number')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_holder_name')->nullable();
            $table->string('card_month')->nullable();
            $table->string('card_year')->nullable();
            $table->string('card_security')->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->dateTime('paid_on')->nullable();
            $table->integer('created_by')->index();
            $table->string('payment_ref_no')->nullable();
            $table->string('account_id')->nullable();
            $table->string('note')->nullable();
            $table->string('document')->nullable();
            $table->string('payment_for')->nullable()->comment('User id who is making payment');
            $table->integer('parent_id')->nullable()->comment('Parent payment id. Used for group payments');
            // $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_payments');
    }
};
