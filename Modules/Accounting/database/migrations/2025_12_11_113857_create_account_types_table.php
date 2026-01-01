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
        if (!Schema::hasTable('account_types')) {
            Schema::create('account_types', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id');
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('parent_account_type_id')->nullable();
                $table->integer('show_balance_as')->default(1)->comment('1=Debit-Credit, 2=Credit-Debit');
                $table->timestamps();
            });
        } else {
            Schema::table('account_types', function (Blueprint $table) {
                if (!Schema::hasColumn('account_types', 'business_id')) {
                    $table->unsignedBigInteger('business_id')->after('id');
                }

                if (!Schema::hasColumn('account_types', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }

                if (!Schema::hasColumn('account_types', 'show_balance_as')) {
                    $table->integer('show_balance_as')->default(1)->comment('1=Debit-Credit, 2=Credit-Debit')->after('parent_account_type_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};
