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
        if (!Schema::hasTable('account_transactions')) {
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
        } else {
            Schema::table('account_transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('account_transactions', 'account_id')) {
                    $table->unsignedBigInteger('account_id')->after('id');
                }

                if (!Schema::hasColumn('account_transactions', 'business_id')) {
                    $table->unsignedBigInteger('business_id')->nullable()->after('account_id');
                }

                if (!Schema::hasColumn('account_transactions', 'transaction_type')) {
                    $table->string('transaction_type')->after('business_id');
                }

                if (!Schema::hasColumn('account_transactions', 'amount')) {
                    $table->decimal('amount', 20, 4)->default(0)->after('transaction_type');
                }

                if (!Schema::hasColumn('account_transactions', 'running_balance')) {
                    $table->decimal('running_balance', 20, 4)->default(0)->after('amount');
                }

                if (!Schema::hasColumn('account_transactions', 'transaction_date')) {
                    $table->date('transaction_date')->after('running_balance');
                }

                if (!Schema::hasColumn('account_transactions', 'reference_no')) {
                    $table->string('reference_no')->nullable()->after('transaction_date');
                }

                if (!Schema::hasColumn('account_transactions', 'reference_type')) {
                    $table->string('reference_type')->nullable()->after('reference_no');
                }

                if (!Schema::hasColumn('account_transactions', 'reference_id')) {
                    $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
                }

                if (!Schema::hasColumn('account_transactions', 'description')) {
                    $table->text('description')->nullable()->after('reference_id');
                }

                if (!Schema::hasColumn('account_transactions', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('description');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
