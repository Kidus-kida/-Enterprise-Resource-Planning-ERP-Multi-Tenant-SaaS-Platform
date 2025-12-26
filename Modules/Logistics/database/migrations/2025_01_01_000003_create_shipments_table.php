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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_no')->unique();
            $table->string('po_reference')->nullable();
            $table->string('vendor');
            $table->string('vendor_country');
            $table->enum('incoterms', ['CIF', 'FOB', 'EXW', 'DDP', 'CFR', 'DAP']);
            $table->string('port_of_loading');
            $table->string('port_of_discharge');
            $table->enum('transport_mode', ['sea', 'air', 'rail', 'truck']);
            $table->enum('status', ['pending', 'vessel_departed', 'at_djibouti', 'in_transit', 'customs_clearance', 'released', 'delivered', 'cancelled'])->default('pending');
            $table->date('expected_arrival');
            $table->date('actual_arrival')->nullable();
            
            $table->foreignId('dry_port_id')->nullable()->constrained('dry_ports');
            $table->unsignedBigInteger('user_id'); // Created by
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
