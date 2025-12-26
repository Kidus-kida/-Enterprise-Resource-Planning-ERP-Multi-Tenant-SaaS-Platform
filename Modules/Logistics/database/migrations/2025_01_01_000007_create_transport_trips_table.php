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
        Schema::create('transport_trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_no')->unique();
            $table->foreignId('container_id')->constrained('containers')->onDelete('cascade');
            
            $table->string('vehicle_plate');
            $table->string('driver_name');
            $table->string('driver_phone')->nullable();
            
            $table->string('origin');
            $table->string('destination');
            $table->integer('distance_km')->nullable();
            
            $table->enum('status', ['scheduled', 'loading', 'in_transit', 'completed', 'delayed', 'cancelled'])->default('scheduled');
            $table->integer('progress')->default(0); // 0-100
            
            $table->datetime('departed_at')->nullable();
            $table->datetime('eta')->nullable();
            $table->datetime('completed_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transport_trips');
    }
};
