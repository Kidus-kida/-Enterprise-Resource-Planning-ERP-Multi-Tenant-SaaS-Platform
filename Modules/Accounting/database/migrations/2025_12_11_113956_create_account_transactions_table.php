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
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('transaction_type'); // debit, credit, opening_balance, etc.
            $table->decimal('amount', 20, 4)->default(0);
            $table->decimal('running_balance', 20, 4)->default(0);
            $table->date('transaction_date');
            $table->string('reference_no')->nullable();
            $table->string('reference_type')->nullable(); // invoice, payment, journal, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            
            // Indexes
            $table->index('account_id');
            $table->index('business_id');
            $table->index('transaction_date');
            $table->index('reference_type');
            $table->index('reference_id');
            $table->index(['reference_type', 'reference_id']);
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
