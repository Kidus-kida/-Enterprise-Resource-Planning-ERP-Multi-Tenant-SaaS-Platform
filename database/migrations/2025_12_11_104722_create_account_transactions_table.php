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
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->decimal('amount', 22, 4);
            $table->enum('type', ['debit', 'credit']);
            $table->enum('sub_type', ['fund_transfer', 'deposit', 'opening_balance', 'withdrawal'])->nullable();
            $table->date('operation_date');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable()->comment('Related transaction if applicable');
            $table->text('note')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index(['account_id', 'operation_date']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }

};
