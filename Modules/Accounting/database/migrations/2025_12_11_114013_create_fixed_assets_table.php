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
        if (!Schema::hasTable('fixed_assets')) {
            Schema::create('fixed_assets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id');
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('account_id')->nullable();
                $table->date('purchase_date')->nullable();
                $table->decimal('purchase_price', 22, 4)->default(0);
                $table->decimal('depreciation_rate', 5, 2)->default(0);
                $table->decimal('current_value', 22, 4)->default(0);
                $table->string('serial_number')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->index('business_id');
                $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
            });
        } else {
            Schema::table('fixed_assets', function (Blueprint $table) {
                if (!Schema::hasColumn('fixed_assets', 'business_id')) {
                    $table->unsignedBigInteger('business_id')->after('id');
                }

                if (!Schema::hasColumn('fixed_assets', 'name')) {
                    $table->string('name')->after('business_id');
                }

                if (!Schema::hasColumn('fixed_assets', 'description')) {
                    $table->text('description')->nullable()->after('name');
                }

                if (!Schema::hasColumn('fixed_assets', 'account_id')) {
                    $table->unsignedBigInteger('account_id')->nullable()->after('description');
                }

                if (!Schema::hasColumn('fixed_assets', 'purchase_date')) {
                    $table->date('purchase_date')->nullable()->after('account_id');
                }

                if (!Schema::hasColumn('fixed_assets', 'purchase_price')) {
                    $table->decimal('purchase_price', 22, 4)->default(0)->after('purchase_date');
                }

                if (!Schema::hasColumn('fixed_assets', 'depreciation_rate')) {
                    $table->decimal('depreciation_rate', 5, 2)->default(0)->after('purchase_price');
                }

                if (!Schema::hasColumn('fixed_assets', 'current_value')) {
                    $table->decimal('current_value', 22, 4)->default(0)->after('depreciation_rate');
                }

                if (!Schema::hasColumn('fixed_assets', 'serial_number')) {
                    $table->string('serial_number')->nullable()->after('current_value');
                }

                if (!Schema::hasColumn('fixed_assets', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('serial_number');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
