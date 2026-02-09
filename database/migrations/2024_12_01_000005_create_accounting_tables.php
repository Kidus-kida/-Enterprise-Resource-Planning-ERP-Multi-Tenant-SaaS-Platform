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
        Schema::create('account_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('name');
            $table->string('class_type');
            $table->integer('parent_account_type_id')->nullable();
            $table->integer('business_id');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('business_id');
            $table->index('parent_account_type_id');
        });

        Schema::create('account_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('name');
            $table->unsignedBigInteger('account_type_id');
            $table->integer('business_id');
            $table->integer('parent_account_group_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('business_id');
            $table->index('account_type_id');
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('name');
            $table->string('gl_code')->nullable();
            $table->unsignedBigInteger('account_type_id');
            $table->text('note')->nullable();
            $table->integer('business_id');
            $table->boolean('is_closed')->default(0);
            $table->unsignedBigInteger('account_group_id')->nullable();
            $table->integer('created_by');
            $table->boolean('is_default')->default(0);
            $table->decimal('opening_balance', 22, 4)->default(0);
            $table->date('as_of_date')->nullable();
            $table->decimal('current_balance', 22, 4)->default(0);
            $table->string('account_sub_type')->nullable();
            $table->integer('parent_account_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('business_id');
            $table->index('account_type_id');
            $table->index('account_group_id');
            $table->index('gl_code');
        });

        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('account_id');
            $table->string('type');
            $table->string('sub_type')->nullable();
            $table->decimal('amount', 22, 4);
            $table->decimal('balance_after', 22, 4)->nullable();
            $table->string('reff_no')->nullable();
            $table->date('operation_date');
            $table->integer('created_by');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->string('transaction_type')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->integer('business_id');
            $table->string('payment_method')->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('card_number')->nullable();
            $table->string('card_holder')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_month')->nullable();
            $table->string('card_year')->nullable();
            $table->string('card_security')->nullable();
            $table->string('transaction_no')->nullable();
            $table->unsignedBigInteger('transfer_to_account_id')->nullable();
            $table->decimal('transfer_amount', 22, 4)->nullable();
            $table->decimal('exchange_rate', 22, 4)->default(1);
            $table->string('currency_code')->nullable();
            $table->boolean('is_reconciled')->default(0);
            $table->date('reconciled_on')->nullable();
            $table->integer('reconciled_by')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();

            $table->unsignedBigInteger('transaction_payment_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('account_id');
            $table->index('type');
            $table->index('transaction_id');
            $table->index('contact_id');
            $table->index('business_id');
            $table->index('operation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('account_groups');
        Schema::dropIfExists('account_types');
    }
};
