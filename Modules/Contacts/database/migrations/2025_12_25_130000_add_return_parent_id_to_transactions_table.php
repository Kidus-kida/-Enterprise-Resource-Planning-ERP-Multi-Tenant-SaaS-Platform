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
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'return_parent_id')) {
                $table->unsignedBigInteger('return_parent_id')->nullable()->after('id');
            }
        });
        
        Schema::table('purchase_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_lines', 'quantity_returned')) {
                $table->decimal('quantity_returned', 22, 4)->default(0)->after('quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('return_parent_id');
        });
        
        Schema::table('purchase_lines', function (Blueprint $table) {
            $table->dropColumn('quantity_returned');
        });
    }
};
