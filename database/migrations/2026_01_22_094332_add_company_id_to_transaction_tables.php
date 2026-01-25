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
        $tables = ['transactions', 'contacts', 'products', 'users', 'transaction_payments'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id')->index();
                    // Foreign key constraint might fail if existing data has inconsistencies or if companies table in different db (unlikely here)
                    // We add it softly.
                    // $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null'); 
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['transactions', 'contacts', 'products', 'users', 'transaction_payments'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'company_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('company_id');
                });
            }
        }
    }
};
