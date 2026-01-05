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

        Schema::table('account_transactions', function (Blueprint $table) {
            // Add related_account_id
            if (!Schema::hasColumn('account_transactions', 'related_account_id')) {
                $table->integer('related_account_id')->nullable()->default(0)->after('account_id');
            }

            // Change type to ENUM and add sub_type ENUM
            DB::statement("ALTER TABLE account_transactions MODIFY COLUMN type ENUM('debit','credit') NOT NULL");
            if (!Schema::hasColumn('account_transactions', 'sub_type')) {
                $table->enum('sub_type', ['opening_balance', 'fund_transfer', 'deposit', 'ledger_show', 'cheque_return_charges', 'purchase_edit', 'fleet_opening_balance', 'vat_payment'])->nullable()->after('type');
            }

            // Replace reference fields with reff_no (keeping existing reference_type and reference_id for compatibility)
            if (!Schema::hasColumn('account_transactions', 'reff_no')) {
                $table->string('reff_no', 191)->nullable()->after('amount');
            }

            // Add operation_date (datetime) - placed after reff_no
            if (!Schema::hasColumn('account_transactions', 'operation_date')) {
                $table->dateTime('operation_date')->nullable()->after('reff_no');
            }

            // Move created_by to the correct position and ensure it's not nullable
            // (it already exists, so we'll just modify it if needed via raw SQL)

            // Add transaction_id
            if (!Schema::hasColumn('account_transactions', 'transaction_id')) {
                $table->integer('transaction_id')->nullable()->after('created_by')->index();
            }

            // Add transaction_payment_id
            if (!Schema::hasColumn('account_transactions', 'transaction_payment_id')) {
                $table->integer('transaction_payment_id')->nullable()->after('transaction_id')->index();
            }

            // Add transfer_transaction_id
            if (!Schema::hasColumn('account_transactions', 'transfer_transaction_id')) {
                $table->integer('transfer_transaction_id')->nullable()->after('transaction_payment_id')->index();
            }

            // Add transaction_sell_line_id
            if (!Schema::hasColumn('account_transactions', 'transaction_sell_line_id')) {
                $table->unsignedInteger('transaction_sell_line_id')->nullable()->after('transfer_transaction_id');
            }

            // Add sell_line_id
            if (!Schema::hasColumn('account_transactions', 'sell_line_id')) {
                $table->integer('sell_line_id')->nullable()->after('transaction_sell_line_id');
            }

            // Add purchase_line_id
            if (!Schema::hasColumn('account_transactions', 'purchase_line_id')) {
                $table->integer('purchase_line_id')->nullable()->after('sell_line_id');
            }

            // Add income_type
            if (!Schema::hasColumn('account_transactions', 'income_type')) {
                $table->string('income_type', 191)->nullable()->after('purchase_line_id');
            }

            // Add note
            if (!Schema::hasColumn('account_transactions', 'note')) {
                $table->text('note')->nullable()->after('income_type');
            }

            // Add slip_no
            if (!Schema::hasColumn('account_transactions', 'slip_no')) {
                $table->string('slip_no', 100)->nullable()->after('note');
            }

            // Add attachment
            if (!Schema::hasColumn('account_transactions', 'attachment')) {
                $table->text('attachment')->nullable()->after('slip_no');
            }

            // Add cheque_number
            if (!Schema::hasColumn('account_transactions', 'cheque_number')) {
                $table->string('cheque_number', 191)->nullable()->after('attachment');
            }

            // Add journal_entry
            if (!Schema::hasColumn('account_transactions', 'journal_entry')) {
                $table->integer('journal_entry')->nullable()->after('cheque_number');
            }

            // Add journal_deleted
            if (!Schema::hasColumn('account_transactions', 'journal_deleted')) {
                $table->boolean('journal_deleted')->default(0)->after('journal_entry');
            }

            // Add installment_id
            if (!Schema::hasColumn('account_transactions', 'installment_id')) {
                $table->unsignedInteger('installment_id')->nullable()->after('journal_deleted');
            }

            // Add payment_option_id
            if (!Schema::hasColumn('account_transactions', 'payment_option_id')) {
                $table->unsignedInteger('payment_option_id')->nullable()->after('installment_id');
            }

            // Add updated_type
            if (!Schema::hasColumn('account_transactions', 'updated_type')) {
                $table->enum('updated_type', ['expense'])->nullable()->after('payment_option_id');
            }

            // Add updated_by
            if (!Schema::hasColumn('account_transactions', 'updated_by')) {
                $table->integer('updated_by')->nullable()->after('updated_type');
            }

            // Add deleted_by
            if (!Schema::hasColumn('account_transactions', 'deleted_by')) {
                $table->unsignedInteger('deleted_by')->nullable()->after('updated_by');
            }

            // deleted_at already exists, just need to ensure it's in right position

            // Add reconcile_status (after deleted_at, before created_at)
            if (!Schema::hasColumn('account_transactions', 'reconcile_status')) {
                $table->boolean('reconcile_status')->default(0)->after('deleted_at');
            }

            // Add interest
            if (!Schema::hasColumn('account_transactions', 'interest')) {
                $table->decimal('interest', 10, 0)->nullable()->after('updated_at');
            }

            // Add bank_name
            if (!Schema::hasColumn('account_transactions', 'bank_name')) {
                $table->string('bank_name', 50)->nullable()->after('interest');
            }

            // Add cheque_numbers (note: plural)
            if (!Schema::hasColumn('account_transactions', 'cheque_numbers')) {
                $table->string('cheque_numbers', 50)->nullable()->after('bank_name');
            }

            // Add cheque_date
            if (!Schema::hasColumn('account_transactions', 'cheque_date')) {
                $table->string('cheque_date', 50)->nullable()->after('cheque_numbers');
            }

            // Add payment_method
            if (!Schema::hasColumn('account_transactions', 'payment_method')) {
                $table->text('payment_method')->nullable()->after('cheque_date');
            }

            // Add fixed_asset_id
            if (!Schema::hasColumn('account_transactions', 'fixed_asset_id')) {
                $table->integer('fixed_asset_id')->nullable()->after('payment_method');
            }

            // Add post_dated_cheque
            if (!Schema::hasColumn('account_transactions', 'post_dated_cheque')) {
                $table->tinyInteger('post_dated_cheque')->nullable()->default(0)->after('fixed_asset_id');
            }

            // Add update_post_dated_cheque
            if (!Schema::hasColumn('account_transactions', 'update_post_dated_cheque')) {
                $table->integer('update_post_dated_cheque')->nullable()->default(0)->after('post_dated_cheque');
            }

            // Add pair_at_id
            if (!Schema::hasColumn('account_transactions', 'pair_at_id')) {
                $table->integer('pair_at_id')->nullable()->after('update_post_dated_cheque');
            }

            // Add postdated_transafer_status
            if (!Schema::hasColumn('account_transactions', 'postdated_transafer_status')) {
                $table->integer('postdated_transafer_status')->default(0)->after('pair_at_id');
            }
        });

        // Ensure created_by is not nullable (skipped to avoid foreign key constraint issues)

        // DB::statement("ALTER TABLE account_transactions MODIFY COLUMN created_by INT NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            // Drop all added columns in reverse order
            $table->dropColumn([
                'postdated_transafer_status',
                'pair_at_id',
                'update_post_dated_cheque',
                'post_dated_cheque',
                'fixed_asset_id',
                'payment_method',
                'cheque_date',
                'cheque_numbers',
                'bank_name',
                'interest',
                'reconcile_status',
                'deleted_by',
                'updated_by',
                'updated_type',
                'payment_option_id',
                'installment_id',
                'journal_deleted',
                'journal_entry',
                'cheque_number',
                'attachment',
                'slip_no',
                'note',
                'income_type',
                'purchase_line_id',
                'sell_line_id',
                'transaction_sell_line_id',
                'transfer_transaction_id',
                'transaction_payment_id',
                'transaction_id',
                'operation_date',
                'reff_no',
                'sub_type',
                'related_account_id',
            ]);

            // Revert type back to string
            DB::statement("ALTER TABLE account_transactions MODIFY COLUMN type VARCHAR(255) NOT NULL");
        });
    }
};
