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
        if (!Schema::hasTable('account_groups')) {
            Schema::create('account_groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id');
                $table->string('name');
                $table->unsignedBigInteger('account_type_id')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->foreign('account_type_id')->references('id')->on('account_types')->onDelete('set null');
            });
        } else {
            Schema::table('account_groups', function (Blueprint $table) {
                if (!Schema::hasColumn('account_groups', 'business_id')) {
                    $table->unsignedBigInteger('business_id')->after('id');
                }

                if (!Schema::hasColumn('account_groups', 'name')) {
                    $table->string('name')->after('business_id');
                }

                if (!Schema::hasColumn('account_groups', 'account_type_id')) {
                    $table->unsignedBigInteger('account_type_id')->nullable()->after('name');
                }

                if (!Schema::hasColumn('account_groups', 'description')) {
                    $table->text('description')->nullable()->after('account_type_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_groups');
    }
};
