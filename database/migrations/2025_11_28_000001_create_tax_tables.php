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
        Schema::create('tax_calculations', function (Blueprint $table) {
            $table->id();
            $table->decimal('income_from', 15, 2);
            $table->decimal('income_to', 15, 2)->nullable();
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('deduction', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payroll_tax_brackets', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_income', 15, 2);
            $table->decimal('max_income', 15, 2)->nullable();
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('deduction', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
        Schema::dropIfExists('payroll_tax_brackets');
        Schema::dropIfExists('tax_calculations');
    }
};
