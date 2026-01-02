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
        if (!Schema::hasTable('account_settings')) {
            Schema::create('account_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id');
                $table->string('key');
                $table->date('date')->nullable();
                $table->unsignedBigInteger('account_id')->nullable();
                $table->unsignedBigInteger('group_id')->nullable();
                $table->decimal('amount', 15, 6)->nullable();
                $table->unsignedBigInteger('at_asset_id')->nullable();
                $table->unsignedBigInteger('at_obe_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->json('settings')->nullable();
                $table->timestamps();
                // Indexes
                $table->index('business_id');
                $table->index('account_id');
                $table->index('group_id');
                $table->index('date');
            });
        } else {
            Schema::table('account_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('account_settings', 'business_id')) {
                    $table->unsignedBigInteger('business_id')->after('id');
                }

                if (!Schema::hasColumn('account_settings', 'key')) {
                    $table->string('key')->after('business_id');
                }

                if (!Schema::hasColumn('account_settings', 'date')) {
                    $table->date('date')->nullable()->after('key');
                }

                if (!Schema::hasColumn('account_settings', 'account_id')) {
                    $table->unsignedBigInteger('account_id')->nullable()->after('date');
                }

                if (!Schema::hasColumn('account_settings', 'group_id')) {
                    $table->unsignedBigInteger('group_id')->nullable()->after('account_id');
                }

                if (!Schema::hasColumn('account_settings', 'amount')) {
                    $table->decimal('amount', 15, 6)->nullable()->after('group_id');
                }

                if (!Schema::hasColumn('account_settings', 'at_asset_id')) {
                    $table->unsignedBigInteger('at_asset_id')->nullable()->after('amount');
                }

                if (!Schema::hasColumn('account_settings', 'at_obe_id')) {
                    $table->unsignedBigInteger('at_obe_id')->nullable()->after('at_asset_id');
                }

                if (!Schema::hasColumn('account_settings', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('at_obe_id');
                }

                if (!Schema::hasColumn('account_settings', 'settings')) {
                    $table->json('settings')->nullable()->after('created_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_settings');
    }
};
