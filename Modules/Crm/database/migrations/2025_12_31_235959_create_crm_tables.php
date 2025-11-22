<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }
        });

        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'status')) {
                $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
            }
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            if (!Schema::hasColumn('follow_ups', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('follow_ups', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down()
    {
     
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });

        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            if (Schema::hasColumn('follow_ups', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
                $table->dropColumn('assigned_to');
            }
            if (Schema::hasColumn('follow_ups', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
