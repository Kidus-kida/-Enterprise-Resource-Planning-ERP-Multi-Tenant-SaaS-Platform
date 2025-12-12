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
        Schema::create('postdated_cheques', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('cheque_number');
            $table->date('cheque_date');
            $table->date('due_date');
            $table->decimal('amount', 20, 4);
            $table->unsignedBigInteger('bank_account_id'); // Account ID for bank
            $table->unsignedBigInteger('contact_id')->nullable(); // Customer/Supplier ID
            $table->boolean('is_received')->default(true); // true = received, false = issued
            $table->boolean('is_realized')->default(false);
            $table->date('realized_date')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable(); // Link to account_transaction
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('bank_account_id')->references('id')->on('accounts')->onDelete('cascade');
            
            // Indexes
            $table->index('business_id');
            $table->index('bank_account_id');
            $table->index('contact_id');
            $table->index('due_date');
            $table->index('is_received');
            $table->index('is_realized');
            $table->index(['is_received', 'is_realized']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postdated_cheques');
    }
};
