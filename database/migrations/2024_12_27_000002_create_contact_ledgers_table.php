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
        Schema::create('contact_ledgers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contact_id');
            $table->decimal('amount', 22, 4);
            $table->string('type');
            $table->string('sub_type')->nullable();
            $table->dateTime('operation_date');
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('transaction_id')->nullable();
            $table->unsignedInteger('transaction_payment_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedInteger('transaction_sell_line_id')->nullable();
            $table->string('income_type')->nullable();
            $table->unsignedInteger('installment_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('contact_id');
            $table->index('transaction_id');
            $table->index('transaction_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_ledgers');
    }
};
