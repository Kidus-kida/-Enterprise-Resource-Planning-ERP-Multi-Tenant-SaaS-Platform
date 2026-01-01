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
        if (!Schema::hasTable('postdated_cheques')) {
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
        } else {
            Schema::table('postdated_cheques', function (Blueprint $table) {
                if (!Schema::hasColumn('postdated_cheques', 'business_id')) {
                    $table->unsignedBigInteger('business_id')->nullable()->after('id');
                }

                if (!Schema::hasColumn('postdated_cheques', 'cheque_number')) {
                    $table->string('cheque_number')->after('business_id');
                }

                if (!Schema::hasColumn('postdated_cheques', 'cheque_date')) {
                    $table->date('cheque_date')->after('cheque_number');
                }

                if (!Schema::hasColumn('postdated_cheques', 'due_date')) {
                    $table->date('due_date')->after('cheque_date');
                }

                if (!Schema::hasColumn('postdated_cheques', 'amount')) {
                    $table->decimal('amount', 20, 4)->after('due_date');
                }

                if (!Schema::hasColumn('postdated_cheques', 'bank_account_id')) {
                    $table->unsignedBigInteger('bank_account_id')->after('amount');
                }

                if (!Schema::hasColumn('postdated_cheques', 'contact_id')) {
                    $table->unsignedBigInteger('contact_id')->nullable()->after('bank_account_id');
                }

                if (!Schema::hasColumn('postdated_cheques', 'is_received')) {
                    $table->boolean('is_received')->default(true)->after('contact_id');
                }

                if (!Schema::hasColumn('postdated_cheques', 'is_realized')) {
                    $table->boolean('is_realized')->default(false)->after('is_received');
                }

                if (!Schema::hasColumn('postdated_cheques', 'realized_date')) {
                    $table->date('realized_date')->nullable()->after('is_realized');
                }

                if (!Schema::hasColumn('postdated_cheques', 'remarks')) {
                    $table->text('remarks')->nullable()->after('realized_date');
                }

                if (!Schema::hasColumn('postdated_cheques', 'transaction_id')) {
                    $table->unsignedBigInteger('transaction_id')->nullable()->after('remarks');
                }

                if (!Schema::hasColumn('postdated_cheques', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('transaction_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postdated_cheques');
    }
};
