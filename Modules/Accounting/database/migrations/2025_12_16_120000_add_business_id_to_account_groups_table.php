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
        if (Schema::hasTable('account_groups')) {
            Schema::table('account_groups', function (Blueprint $table) {
                if (!Schema::hasColumn('account_groups', 'business_id')) {
                    $table->unsignedBigInteger('business_id')->nullable()->after('id');
                    $table->index('business_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_groups', function (Blueprint $table) {
            $table->dropColumn('business_id');
        });
    }
};
