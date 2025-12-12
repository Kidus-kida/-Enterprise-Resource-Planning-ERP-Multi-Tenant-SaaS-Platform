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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable(); // For compatibility, though likely unused in single-tenant
            $table->string('type')->index(); // supplier, customer, both
            $table->string('supplier_business_name')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('contact_id')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->text('address')->nullable();
            $table->string('landmark')->nullable();
            $table->string('mobile')->nullable();
            $table->string('landline')->nullable();
            $table->string('alternate_number')->nullable();
            $table->integer('pay_term_number')->nullable();
            $table->string('pay_term_type')->nullable();
            $table->decimal('credit_limit', 22, 4)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_default')->default(0);
            $table->unsignedBigInteger('customer_group_id')->nullable();
            $table->unsignedBigInteger('supplier_group_id')->nullable();
            $table->string('image')->nullable();
            $table->string('signature')->nullable();
            $table->json('sub_customers')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('custom_field1')->nullable();
            $table->string('custom_field2')->nullable();
            $table->string('custom_field3')->nullable();
            $table->string('custom_field4')->nullable();
            $table->boolean('active')->default(1);
            $table->boolean('is_property')->default(0);
            $table->boolean('should_notify')->default(0);
            $table->date('contact_transaction_date')->nullable();
            $table->json('notification_contacts')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
