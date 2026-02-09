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
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->text('shipping_details')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_status')->nullable();
            $table->string('delivered_to')->nullable();
            $table->decimal('shipping_charges', 22, 4)->default(0);

            $table->foreignId('dry_port_id')->nullable()->constrained('dry_ports');
            $table->unsignedBigInteger('user_id'); // Created by
            $table->decimal('value_etb', 15, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('business_id');
            $table->index('transaction_id');
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
