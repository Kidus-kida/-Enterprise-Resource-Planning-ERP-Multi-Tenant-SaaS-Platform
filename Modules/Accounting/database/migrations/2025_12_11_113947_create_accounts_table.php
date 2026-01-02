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
        if (!Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id')->nullable();
                $table->string('name');
                $table->string('account_number')->nullable()->unique();
                $table->unsignedBigInteger('account_type_id');
                $table->unsignedBigInteger('asset_type')->nullable(); // Account Group ID
                $table->unsignedBigInteger('parent_account_id')->nullable();
                $table->decimal('opening_balance', 20, 4)->default(0);
                $table->decimal('current_balance', 20, 4)->default(0);
                $table->text('note')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_main_account')->default(0);
                $table->string('is_need_cheque', 1)->default('N'); // Y/N
                $table->boolean('is_closed')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Foreign keys
                $table->foreign('account_type_id')->references('id')->on('account_types')->onDelete('cascade');
                $table->foreign('asset_type')->references('id')->on('account_groups')->onDelete('set null');
                $table->foreign('parent_account_id')->references('id')->on('accounts')->onDelete('set null');

                // Indexes
                $table->index('business_id');
                $table->index('account_type_id');
                $table->index('asset_type');
                $table->index('parent_account_id');
                $table->index('is_closed');
            });
        } else {
            Schema::table('accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('accounts', 'business_id')) {
                    $table->unsignedBigInteger('business_id')->nullable()->after('id');
                }

                if (!Schema::hasColumn('accounts', 'name')) {
                    $table->string('name')->after('business_id');
                }

                if (!Schema::hasColumn('accounts', 'account_number')) {
                    $table->string('account_number')->nullable()->unique()->after('name');
                }

                if (!Schema::hasColumn('accounts', 'account_type_id')) {
                    $table->unsignedBigInteger('account_type_id')->after('account_number');
                }

                if (!Schema::hasColumn('accounts', 'asset_type')) {
                    $table->unsignedBigInteger('asset_type')->nullable()->after('account_type_id');
                }

                if (!Schema::hasColumn('accounts', 'parent_account_id')) {
                    $table->unsignedBigInteger('parent_account_id')->nullable()->after('asset_type');
                }

                if (!Schema::hasColumn('accounts', 'opening_balance')) {
                    $table->decimal('opening_balance', 20, 4)->default(0)->after('parent_account_id');
                }

                if (!Schema::hasColumn('accounts', 'current_balance')) {
                    $table->decimal('current_balance', 20, 4)->default(0)->after('opening_balance');
                }

                if (!Schema::hasColumn('accounts', 'note')) {
                    $table->text('note')->nullable()->after('current_balance');
                }

                if (!Schema::hasColumn('accounts', 'description')) {
                    $table->text('description')->nullable()->after('note');
                }

                if (!Schema::hasColumn('accounts', 'is_main_account')) {
                    $table->boolean('is_main_account')->default(0)->after('description');
                }

                if (!Schema::hasColumn('accounts', 'is_need_cheque')) {
                    $table->string('is_need_cheque', 1)->default('N')->after('is_main_account');
                }

                if (!Schema::hasColumn('accounts', 'is_closed')) {
                    $table->boolean('is_closed')->default(0)->after('is_need_cheque');
                }

                if (!Schema::hasColumn('accounts', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('is_closed');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
