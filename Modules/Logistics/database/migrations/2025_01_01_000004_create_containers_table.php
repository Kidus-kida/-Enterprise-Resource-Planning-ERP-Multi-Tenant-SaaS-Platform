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
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('container_no');
            $table->string('seal_no')->nullable();
            $table->enum('size', ['20ft', '40ft', '45ft', 'LCL']);
            $table->enum('type', ['dry', 'reefer', 'flat_rack', 'open_top', 'tank'])->default('dry');
            $table->string('shipping_line');
            $table->string('status')->default('pending'); // Mirrors shipment status usually
            $table->integer('demurrage_days')->default(0);
            $table->datetime('arrived_at_djibouti')->nullable();
            $table->datetime('arrived_at_dry_port')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('container_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('containers');
    }
};
