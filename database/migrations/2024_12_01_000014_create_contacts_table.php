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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->enum('type', ['customer', 'supplier', 'both'])->default('customer');
            $table->string('supplier_business_name')->nullable();
            $table->string('name')->nullable();
            $table->string('prefix')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('contact_id')->nullable();
            $table->enum('contact_status', ['active', 'inactive'])->default('active');
            $table->string('tax_number')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->char('zip_code', 7)->nullable();
            $table->date('dob')->nullable();
            $table->string('mobile');
            $table->string('landline')->nullable();
            $table->string('alternate_number')->nullable();
            $table->integer('pay_term_number')->nullable();
            $table->enum('pay_term_type', ['days', 'months'])->nullable();
            $table->unsignedInteger('credit_limit')->nullable();
            $table->integer('created_by')->unsigned();
            $table->decimal('balance', 22, 4)->default(0);
            $table->decimal('total_rp', 20, 4)->default(0)->comment('rp is the short form of reward points');
            $table->decimal('total_rp_used', 20, 4)->default(0)->comment('rp is the short form of reward points');
            $table->decimal('total_rp_expired', 20, 4)->default(0)->comment('rp is the short form of reward points');
            $table->boolean('is_default')->default(0);
            $table->string('shipping_address')->nullable();
            $table->string('position')->nullable();
            $table->text('customer_group_id')->nullable();
            $table->string('custom_field1')->nullable();
            $table->string('custom_field2')->nullable();
            $table->string('custom_field3')->nullable();
            $table->string('custom_field4')->nullable();
            $table->string('custom_field5')->nullable();
            $table->string('custom_field6')->nullable();
            $table->string('custom_field7')->nullable();
            $table->string('custom_field8')->nullable();
            $table->string('custom_field9')->nullable();
            $table->string('custom_field10')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('business_id');
            $table->index('created_by');
            $table->index('type');
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
