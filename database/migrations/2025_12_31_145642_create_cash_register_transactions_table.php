<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashRegisterTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_register_transactions', function (Blueprint $blueprint) {
            $blueprint->bigIncrements('id');
            $blueprint->unsignedBigInteger('cash_register_id')->index();
            $blueprint->decimal('amount', 22, 4)->default(0);
            $blueprint->enum('pay_method', ['cash', 'card', 'cheque', 'bank_transfer', 'other', 'custom_pay_1', 'custom_pay_2', 'custom_pay_3'])->nullable();
            $blueprint->enum('type', ['debit', 'credit']);
            $blueprint->enum('transaction_type', ['initial', 'sell', 'transfer', 'refund']);
            $blueprint->unsignedBigInteger('transaction_id')->nullable()->index();
            $blueprint->timestamps();
            
            $blueprint->foreign('cash_register_id')->references('id')->on('cash_registers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_register_transactions');
    }
}
