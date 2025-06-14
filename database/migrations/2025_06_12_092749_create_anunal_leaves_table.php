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
        Schema::create('anunal_leaves', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->decimal('current_year')->default(0.00);
            $table->decimal('previous_year')->default(0.00);
            $table->decimal('year_bpy')->default(0.00);
            $table->decimal('per_month')->default(0.00);
            $table->integer('per_year')->default(0);
            $table->integer('total_anunal_leave')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anunal_leaves');
    }
};
