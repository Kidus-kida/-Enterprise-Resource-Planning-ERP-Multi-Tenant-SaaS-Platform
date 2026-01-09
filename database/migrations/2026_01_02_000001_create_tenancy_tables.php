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
        if (!Schema::hasTable('packages')) {
            Schema::create('packages', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('price', 22, 4)->default(0);
                $table->unsignedInteger('currency_id')->nullable();
                $table->enum('interval', ['days', 'months', 'years'])->default('months');
                $table->integer('interval_count')->default(1);
                $table->integer('trial_days')->default(0);
                $table->integer('location_count')->default(0);
                $table->integer('user_count')->default(0);
                $table->integer('product_count')->default(0);
                $table->integer('invoice_count')->default(0);
                $table->json('custom_permissions')->nullable();
                $table->boolean('is_active')->default(1);
                $table->boolean('is_private')->default(0);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('package_id')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->json('package_details')->nullable();
                $table->json('module_activation_details')->nullable();
                $table->string('paid_via')->nullable();
                $table->string('payment_transaction_id')->nullable();
                $table->enum('status', ['approved', 'waiting', 'declined'])->default('waiting');
                $table->unsignedBigInteger('created_id')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('business_id');
                $table->index('package_id');
                $table->index('status');
            });
        }

        if (!Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->unsignedBigInteger('business_id')->unique()->nullable();
                $table->string('database_name')->nullable();
                $table->json('data')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('domains')) {
            Schema::create('domains', function (Blueprint $table) {
                $table->id();
                $table->string('domain')->unique();
                $table->string('tenant_id');
                $table->timestamps();
                
                $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('manual_payments')) {
            Schema::create('manual_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id');
                $table->unsignedBigInteger('subscription_id')->nullable();
                $table->decimal('amount', 22, 4);
                $table->string('currency', 10)->default('ETB');
                $table->string('payment_method')->nullable();
                $table->string('reference_number')->nullable();
                $table->string('receipt_path')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();

                $table->index('business_id');
                $table->index('subscription_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_payments');
        Schema::dropIfExists('domains');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('packages');
    }
};
