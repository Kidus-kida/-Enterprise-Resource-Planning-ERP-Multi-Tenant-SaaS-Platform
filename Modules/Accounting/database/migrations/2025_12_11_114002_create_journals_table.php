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
        if (!Schema::hasTable('journals')) {
            Schema::create('journals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable();
                $table->string('journal_no')->unique();
                $table->date('date');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('account_id');
                $table->string('type'); // debit or credit
                $table->decimal('amount', 20, 4);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Foreign keys
                $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

                // Indexes
                $table->index('business_id');
                $table->index('account_id');
                $table->index('date');
                $table->index('type');
            });
        } else {
            Schema::table('journals', function (Blueprint $table) {
                if (!Schema::hasColumn('journals', 'business_id')) {
                    $table->unsignedBigInteger('business_id')->nullable()->after('id');
                }

                if (!Schema::hasColumn('journals', 'journal_no')) {
                    $table->string('journal_no')->unique()->after('business_id');
                }

                if (!Schema::hasColumn('journals', 'date')) {
                    $table->date('date')->after('journal_no');
                }

                if (!Schema::hasColumn('journals', 'description')) {
                    $table->text('description')->nullable()->after('date');
                }

                if (!Schema::hasColumn('journals', 'account_id')) {
                    $table->unsignedBigInteger('account_id')->after('description');
                }

                if (!Schema::hasColumn('journals', 'type')) {
                    $table->string('type')->after('account_id');
                }

                if (!Schema::hasColumn('journals', 'amount')) {
                    $table->decimal('amount', 20, 4)->after('type');
                }

                if (!Schema::hasColumn('journals', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('amount');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
