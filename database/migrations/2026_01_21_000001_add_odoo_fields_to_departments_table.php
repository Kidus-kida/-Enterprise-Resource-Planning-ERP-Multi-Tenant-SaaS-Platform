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
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'manager_id')) {
                $table->unsignedBigInteger('manager_id')->nullable()->after('name');
            }
            if (!Schema::hasColumn('departments', 'color')) {
                $table->string('color')->nullable()->after('manager_id');
            }
            if (!Schema::hasColumn('departments', 'company_name')) {
                $table->string('company_name')->nullable()->after('color');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['manager_id', 'color', 'company_name']);
        });
    }
};
