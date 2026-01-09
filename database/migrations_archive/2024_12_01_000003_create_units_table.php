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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('actual_name');
            $table->string('short_name');
            $table->boolean('allow_decimal')->default(0);
            $table->unsignedBigInteger('base_unit_id')->nullable();
            $table->decimal('base_unit_multiplier', 20, 4)->nullable();
            $table->integer('created_by')->unsigned();
            $table->boolean('is_property')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('business_id');
            $table->index('base_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
