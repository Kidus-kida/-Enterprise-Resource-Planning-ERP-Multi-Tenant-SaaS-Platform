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
        Schema::table('businesses', function (Blueprint $table) {
            if (!Schema::hasColumn('businesses', 'tenant_id')) {
                $table->string('tenant_id')->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('businesses', 'subdomain')) {
                $table->string('subdomain')->unique()->nullable()->after('tenant_id');
            }
            if (!Schema::hasColumn('businesses', 'is_active')) {
                $table->boolean('is_active')->default(1)->after('subdomain');
            }
            if (!Schema::hasColumn('businesses', 'package_id')) {
                $table->unsignedBigInteger('package_id')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('businesses', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('package_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['tenant_id', 'subdomain', 'is_active', 'package_id', 'created_by']);
        });
    }
};
