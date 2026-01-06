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
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->decimal('amount', 22, 4);
            $table->enum('type', ['debit', 'credit']);
            $table->string('sub_type')->nullable();
            $table->dateTime('operation_date');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->unsignedBigInteger('transaction_payment_id')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('transaction_sell_line_id')->nullable();
            $table->string('income_type')->nullable();
            $table->unsignedBigInteger('installment_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better query performance
            $table->index('contact_id');
            $table->index('transaction_id');
            $table->index('transaction_payment_id');
            $table->index('operation_date');
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
