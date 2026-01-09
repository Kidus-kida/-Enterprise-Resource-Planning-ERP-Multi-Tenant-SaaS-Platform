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
        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('parent_sell_line_id')->nullable()->after('id');
            $table->string('children_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_sell_lines', function (Blueprint $table) {
            //
            $table->dropColumn('parent_sell_line_id');
        });
    }
};
