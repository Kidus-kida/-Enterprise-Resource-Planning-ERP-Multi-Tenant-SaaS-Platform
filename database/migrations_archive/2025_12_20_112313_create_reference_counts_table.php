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
        if (!Schema::hasTable('reference_counts')) {
            Schema::create('reference_counts', function (Blueprint $table) {
                $table->id();
                $table->string('ref_type')->unique();
                $table->unsignedBigInteger('ref_count')->default(0);
                $table->Integer('business_id')->default(1);
                $table->timestamps();
            });
        } else {
            Schema::table('reference_counts', function (Blueprint $table) {
                if (!Schema::hasColumn('reference_counts', 'ref_type')) {
                    $table->string('ref_type')->unique()->after('id');
                }
                if (!Schema::hasColumn('reference_counts', 'ref_count')) {
                    $table->unsignedBigInteger('ref_count')->default(0)->after('ref_type');
                }
                if (!Schema::hasColumn('reference_counts', 'business_id')) {
                    $table->Integer('business_id')->default(1)->after('ref_count');
                }
                if (!Schema::hasColumn('reference_counts', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reference_counts');
    }
};
