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
        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'subscribed_user_count')) {
                $table->integer('subscribed_user_count')->nullable()->after('package_id');
            }
            if (!Schema::hasColumn('subscriptions', 'base_price')) {
                $table->decimal('base_price', 10, 2)->default(0)->after('module_activation_details');
            }
            if (!Schema::hasColumn('subscriptions', 'addons_price')) {
                $table->decimal('addons_price', 10, 2)->default(0)->after('base_price');
            }
            if (!Schema::hasColumn('subscriptions', 'total_price')) {
                $table->decimal('total_price', 10, 2)->default(0)->after('addons_price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $columns = ['subscribed_user_count', 'base_price', 'addons_price', 'total_price'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('subscriptions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
