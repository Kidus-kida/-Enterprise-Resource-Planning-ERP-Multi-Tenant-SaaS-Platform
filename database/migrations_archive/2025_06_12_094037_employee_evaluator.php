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
        Schema::create('employee_evaluator', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id'); // this is the employee being evaluated
            $table->unsignedBigInteger('evaluator_id'); // this is the evaluator, could be a supervisor or manager
            $table->timestamps();

            $table->unique(['employee_id', 'evaluator_id']);
            $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('evaluator_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_evaluator');
    }
};
