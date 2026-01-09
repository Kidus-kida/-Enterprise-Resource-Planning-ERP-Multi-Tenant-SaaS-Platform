<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPercentageScoreToPerformancesTable extends Migration
{
    public function up(): void
    {
        Schema::table('performances', function (Blueprint $table) {
            $table->decimal('percentage_score', 5, 2)->nullable()->after('average_score');
        });
    }

    public function down(): void
    {
        Schema::table('performances', function (Blueprint $table) {
            $table->dropColumn('percentage_score');
        });
    }
}
