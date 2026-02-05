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
            $table->unsignedBigInteger('company_id')->nullable()->index();
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

            // Additional fields from later migrations
            $table->string('landmark')->nullable();
            $table->string('image')->nullable();
            $table->string('signature')->nullable();
            $table->boolean('is_payee')->default(0);
            $table->json('sub_customers')->nullable();
            $table->string('vat_number')->nullable();
            $table->boolean('active')->default(1);
            $table->boolean('is_property')->default(0);
            $table->boolean('should_notify')->default(0);
            $table->date('contact_transaction_date')->nullable();
            $table->json('notification_contacts')->nullable();
            $table->integer('supplier_group_id')->nullable();
            $table->text('address_2')->nullable();
            $table->text('address_3')->nullable();
            $table->string('geo_location')->nullable();
            $table->string('nic_number', 20)->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('sub_customer')->default(0);
            $table->boolean('sol_with_approval')->default(0);

            $table->softDeletes();
            $table->timestamps();

            $table->index('business_id');
            $table->index('created_by');
            $table->index('type');
            $table->index('contact_status');
        });

        Schema::create('contact_ledgers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('business_id');
            $table->string('transaction_type');
            $table->string('reference_no')->nullable();
            $table->decimal('amount', 22, 4);
            $table->decimal('balance', 22, 4);
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->integer('created_by');
            $table->timestamps();

            $table->index('contact_id');
            $table->index('business_id');
            $table->index('transaction_date');
        });

        Schema::create('client_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('company_name')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_details');
        Schema::dropIfExists('contact_ledgers');
        Schema::dropIfExists('contacts');
    }
};
