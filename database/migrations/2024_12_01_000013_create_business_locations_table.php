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
        Schema::create('business_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('location_id')->nullable();
            $table->string('name');
            $table->string('landmark')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->char('zip_code', 7)->nullable();
            $table->string('invoice_scheme_id')->nullable();
            $table->string('invoice_layout_id')->nullable();
            $table->string('selling_price_group_id')->nullable();
            $table->string('print_receipt_on_invoice')->nullable();
            $table->integer('receipt_printer_type')->nullable();
            $table->text('printer_id')->nullable();
            $table->string('mobile')->nullable();
            $table->string('alternate_number')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->unsignedInteger('featured_products')->nullable();
            $table->boolean('is_active')->default(1);
            $table->text('default_payment_accounts')->nullable();
            $table->text('custom_field1')->nullable();
            $table->text('custom_field2')->nullable();
            $table->text('custom_field3')->nullable();
            $table->text('custom_field4')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('business_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_locations');
    }
};
