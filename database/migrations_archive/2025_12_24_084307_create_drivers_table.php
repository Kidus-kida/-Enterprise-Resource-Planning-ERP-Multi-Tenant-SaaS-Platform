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
        Schema::create('drivers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('business_id')->unsigned();
            $table->string('employee_no')->nullable();
            $table->string('driver_name');
            $table->string('nic_number')->nullable();
            $table->string('dl_number')->nullable();
            $table->date('joined_date')->nullable();
            $table->integer('created_by')->unsigned();
            $table->string('dl_type')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('car_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
