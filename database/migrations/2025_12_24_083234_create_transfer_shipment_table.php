<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('transfer_shipment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('request_id')->index(); // comma separated IDs or handle as text
            $table->integer('driver_id')->index();
            $table->string('shipment_status');
            $table->date('assigned_date');
            $table->date('delivered_date')->nullable();
            $table->integer('created_by')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_shipment');
    }
};
