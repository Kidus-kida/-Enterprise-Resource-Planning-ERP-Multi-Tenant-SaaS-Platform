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
        Schema::create('shift_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shift_id')->nullable()->comment('Null means user is off for this day');
            $table->date('date');
            $table->boolean('is_active')->default(true);
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_overrides');
    }
};
