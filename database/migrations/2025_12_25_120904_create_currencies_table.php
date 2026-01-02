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
        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('country', 100);
                $table->string('currency', 100);
                $table->string('code', 25);
                $table->string('symbol', 25);
                $table->string('thousand_separator', 10);
                $table->string('decimal_separator', 10);
                $table->timestamps();
            });
        } else {
            Schema::table('currencies', function (Blueprint $table) {
                if (!Schema::hasColumn('currencies', 'country')) {
                    $table->string('country', 100)->after('id');
                }
                if (!Schema::hasColumn('currencies', 'currency')) {
                    $table->string('currency', 100)->after('country');
                }
                if (!Schema::hasColumn('currencies', 'code')) {
                    $table->string('code', 25)->after('currency');
                }
                if (!Schema::hasColumn('currencies', 'symbol')) {
                    $table->string('symbol', 25)->after('code');
                }
                if (!Schema::hasColumn('currencies', 'thousand_separator')) {
                    $table->string('thousand_separator', 10)->after('symbol');
                }
                if (!Schema::hasColumn('currencies', 'decimal_separator')) {
                    $table->string('decimal_separator', 10)->after('thousand_separator');
                }
                if (!Schema::hasColumn('currencies', 'created_at')) {
                    $table->timestamps();
                }
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
