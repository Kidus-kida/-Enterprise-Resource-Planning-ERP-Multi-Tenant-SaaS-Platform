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
        Schema::create('shift_rotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->index();
            $table->string('name');
            $table->enum('frequency_type', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->integer('frequency_interval')->default(1);
            $table->date('start_date');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('shift_rotation_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shift_rotation_id')->index();
            $table->unsignedBigInteger('shift_id')->index();
            $table->integer('step_order');
            $table->timestamps();
        });

        Schema::create('user_shift_rotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('shift_rotation_id')->index();
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_shift_rotations');
        Schema::dropIfExists('shift_rotation_steps');
        Schema::dropIfExists('shift_rotations');
    }
};
