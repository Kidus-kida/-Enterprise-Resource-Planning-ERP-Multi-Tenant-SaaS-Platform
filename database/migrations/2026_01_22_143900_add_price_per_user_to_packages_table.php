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
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'price_per_user')) {
                $table->decimal('price_per_user', 10, 2)->default(0)->after('price');
            }
            if (!Schema::hasColumn('packages', 'min_users')) {
                $table->integer('min_users')->default(1)->after('user_count');
            }
            if (!Schema::hasColumn('packages', 'is_per_user_pricing')) {
                $table->boolean('is_per_user_pricing')->default(0)->after('is_private');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $columns = ['price_per_user', 'min_users', 'is_per_user_pricing'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('packages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
