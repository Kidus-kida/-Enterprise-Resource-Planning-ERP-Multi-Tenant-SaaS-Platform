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
        if (Schema::hasTable('currencies')) {
            Schema::table('currencies', function (Blueprint $table) {
                if (!Schema::hasColumn('currencies', 'country')) $table->string('country')->nullable();
                if (!Schema::hasColumn('currencies', 'currency')) $table->string('currency')->nullable();
                if (!Schema::hasColumn('currencies', 'code')) $table->string('code')->nullable(); // Might be duplicate index issue if unique added again, assuming existing is fine
                if (!Schema::hasColumn('currencies', 'symbol')) $table->string('symbol')->nullable();
                if (!Schema::hasColumn('currencies', 'exchange_rate')) $table->decimal('exchange_rate', 20, 8)->default(1);
                if (!Schema::hasColumn('currencies', 'thousand_separator')) $table->boolean('thousand_separator')->default(1);
                if (!Schema::hasColumn('currencies', 'decimal_separator')) $table->boolean('decimal_separator')->default(1);
                if (!Schema::hasColumn('currencies', 'currency_symbol_placement')) $table->string('currency_symbol_placement')->default('before');
                if (!Schema::hasColumn('currencies', 'created_by')) $table->integer('created_by')->default(1);
                if (!Schema::hasColumn('currencies', 'created_at')) $table->timestamp('created_at')->nullable();
                if (!Schema::hasColumn('currencies', 'updated_at')) $table->timestamp('updated_at')->nullable();
            });
        } else {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('country');
                $table->string('currency');
                $table->string('code')->unique();
                $table->string('symbol');
                $table->decimal('exchange_rate', 20, 8)->default(1);
                $table->boolean('thousand_separator')->default(1);
                $table->boolean('decimal_separator')->default(1);
                $table->string('currency_symbol_placement')->default('before');
                $table->integer('created_by');
                $table->timestamps();

                $table->index('code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
