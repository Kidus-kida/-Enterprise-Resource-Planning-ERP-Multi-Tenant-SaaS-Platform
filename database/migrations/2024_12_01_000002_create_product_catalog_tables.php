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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('name');
            $table->unsignedBigInteger('business_id');
            $table->text('description')->nullable();
            $table->integer('parent_id')->nullable();
            $table->string('category_type')->default('product');

            // Accounting fields
            $table->integer('income_account_id')->nullable();
            $table->integer('expense_account_id')->nullable();
            $table->integer('asset_account_id')->nullable();
            $table->integer('cogs_account_id')->nullable();
            $table->integer('inventory_account_id')->nullable();
            $table->integer('sales_return_account_id')->nullable();
            $table->integer('purchase_return_account_id')->nullable();
            $table->integer('discount_account_id')->nullable();
            $table->integer('vat_input_account_id')->nullable();
            $table->integer('vat_output_account_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('business_id');
            $table->index('parent_id');
        });

        Schema::create('merged_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->timestamps();

            $table->index('business_id');
            $table->index('category_id');
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->index('business_id');
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('business_id');
            $table->string('actual_name');
            $table->string('short_name');
            $table->boolean('allow_decimal')->default(0);
            $table->integer('base_unit_id')->nullable();
            $table->decimal('base_unit_multiplier', 20, 4)->nullable();
            $table->integer('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->index('business_id');
        });

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->decimal('amount', 22, 4);
            $table->boolean('is_tax_group')->default(0);
            $table->integer('created_by');
            $table->timestamps();
            $table->softDeletes();
            $table->index('business_id');
        });

        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('name');
            $table->unsignedBigInteger('business_id');
            $table->text('description')->nullable();
            $table->integer('duration');
            $table->enum('duration_type', ['days', 'months', 'years'])->default('months');
            $table->timestamps();

            $table->index('business_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranties');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('units');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('merged_sub_categories');
        Schema::dropIfExists('categories');
    }
};
