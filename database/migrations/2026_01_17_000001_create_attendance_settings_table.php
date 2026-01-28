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
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('category', 50)->index(); // time_rules, location_rules, penalties
            $table->text('value');
            $table->string('type', 20)->default('string'); // string, integer, float, boolean, json
            $table->string('label');
            $table->text('description')->nullable();
            $table->text('validation_rules')->nullable(); // JSON encoded validation rules
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index(['category', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
