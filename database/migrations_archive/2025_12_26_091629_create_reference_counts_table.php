<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('reference_counts')) {
            Schema::create('reference_counts', function (Blueprint $table) {
                $table->id();
                $table->string('ref_type');
                $table->integer('business_id');
                $table->integer('ref_count');
                $table->timestamps();
            });
        } else {
            Schema::table('reference_counts', function (Blueprint $table) {
                if (!Schema::hasColumn('reference_counts', 'ref_type')) {
                    $table->string('ref_type')->after('id');
                }
                if (!Schema::hasColumn('reference_counts', 'business_id')) {
                    $table->integer('business_id')->after('ref_type');
                }
                if (!Schema::hasColumn('reference_counts', 'ref_count')) {
                    $table->integer('ref_count')->after('business_id');
                }
                if (!Schema::hasColumn('reference_counts', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reference_counts');
    }
};
