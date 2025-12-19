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
        Schema::create('customer_statements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('statement_no')->nullable();
            $table->unsignedBigInteger('customer_id')->index();
            $table->date('print_date')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->unsignedBigInteger('added_by')->nullable(); // User ID
            $table->unsignedBigInteger('location_id')->nullable();
             $table->string('logo')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        
        Schema::create('customer_statement_details', function (Blueprint $table) {
             $table->id();
             $table->unsignedBigInteger('business_id');
             $table->unsignedBigInteger('statement_id')->index();
             $table->date('date')->nullable();
             $table->string('location')->nullable();
             $table->string('invoice_no')->nullable();
             $table->string('customer_reference')->nullable();
             $table->string('order_no')->nullable();
             $table->string('vehicle_number')->nullable();
             $table->string('route_name')->nullable();
             $table->date('order_date')->nullable();
             $table->string('product')->nullable();
             $table->decimal('unit_price', 22, 4)->default(0);
             $table->decimal('qty', 22, 4)->default(0);
             $table->decimal('invoice_amount', 22, 4)->default(0);
             $table->decimal('due_amount', 22, 4)->default(0);
             $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_statement_details');
        Schema::dropIfExists('customer_statements');
    }
};
