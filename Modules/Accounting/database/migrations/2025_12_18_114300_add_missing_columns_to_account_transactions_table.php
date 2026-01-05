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
            if (!Schema::hasColumn('account_transactions', 'transaction_id')) {
                $table->unsignedBigInteger('transaction_id')->nullable()->after('account_id');
            }

            if (!Schema::hasColumn('account_transactions', 'type')) {
                $table->string('type')->after('transaction_type'); // We need 'type' column as per query
            }

            if (!Schema::hasColumn('account_transactions', 'operation_date')) {
                $table->dateTime('operation_date')->nullable()->after('transaction_date');
            }

            if (!Schema::hasColumn('account_transactions', 'transaction_payment_id')) {
                $table->unsignedBigInteger('transaction_payment_id')->nullable()->after('transaction_id');
            }

            if (!Schema::hasColumn('account_transactions', 'sub_type')) {
                $table->string('sub_type')->nullable()->after('type');
            }

            if (!Schema::hasColumn('account_transactions', 'note')) {
                $table->text('note')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
            $table->dropColumn('type');
            $table->dropColumn('operation_date');
            $table->dropColumn('transaction_payment_id');
            $table->dropColumn('sub_type');
            $table->dropColumn('note');
        });
    }
};