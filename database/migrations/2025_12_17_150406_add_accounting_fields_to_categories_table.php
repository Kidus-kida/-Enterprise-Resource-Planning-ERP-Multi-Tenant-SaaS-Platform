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
        Schema::table('categories', function (Blueprint $table) {
            //$table->string('add_related_account')->nullable()->after('description');
            //$table->unsignedBigInteger('cogs_account_id')->nullable()->after('add_related_account');
            //$table->unsignedBigInteger('sales_income_account_id')->nullable()->after('cogs_account_id');
            //$table->boolean('weight_excess_loss_applicable')->default(0)->after('sales_income_account_id');
            //$table->unsignedBigInteger('weight_loss_expense_account_id')->nullable()->after('weight_excess_loss_applicable');
            //$table->unsignedBigInteger('weight_excess_income_account_id')->nullable()->after('weight_loss_expense_account_id');
            //$table->string('vat_exempted')->nullable()->after('weight_excess_income_account_id');
            //$table->string('vat_based_on')->nullable()->after('vat_exempted');
            //$table->string('apply_vat_on')->nullable()->after('vat_based_on');
            //$table->decimal('profit_percentage', 8, 2)->nullable()->after('apply_vat_on');
            //$table->unsignedBigInteger('price_reduction_acc')->nullable()->after('profit_percentage');
            //$table->unsignedBigInteger('price_increment_acc')->nullable()->after('price_reduction_acc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'add_related_account',
                'cogs_account_id',
                'sales_income_account_id',
                'weight_excess_loss_applicable',
                'weight_loss_expense_account_id',
                'weight_excess_income_account_id',
                'vat_exempted',
                'vat_based_on',
                'apply_vat_on',
                'profit_percentage',
                'price_reduction_acc',
                'price_increment_acc',
            ]);
        });
    }
};
