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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('license_number')->unique();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->date('license_expiry')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->index('license_number');
        });

        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->unique();
            $table->string('model');
            $table->string('make')->nullable();
            $table->string('year')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->index('plate_number');
        });

        Schema::create('stock_transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->unsignedBigInteger('from_location_id');
            $table->unsignedBigInteger('to_location_id');
            $table->date('transfer_date');
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->integer('requested_by');
            $table->integer('approved_by')->nullable();
            $table->date('approved_date')->nullable();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->foreignId('car_id')->nullable()->constrained('cars')->onDelete('set null');
            $table->timestamps();

            $table->index('from_location_id');
            $table->index('to_location_id');
            $table->index('status');
        });

        Schema::create('transfer_shipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_request_id')->constrained('stock_transfer_requests')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->foreignId('car_id')->nullable()->constrained('cars')->onDelete('set null');
            $table->date('shipment_date');
            $table->string('status')->default('in_transit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_shipment');
        Schema::dropIfExists('stock_transfer_requests');
        Schema::dropIfExists('cars');
        Schema::dropIfExists('drivers');
    }
};
