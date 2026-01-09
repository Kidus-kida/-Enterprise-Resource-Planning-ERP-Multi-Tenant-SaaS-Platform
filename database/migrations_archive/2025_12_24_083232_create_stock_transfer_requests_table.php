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
        Schema::create('stock_transfer_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('business_id')->index();
            $table->integer('request_location')->index();
            $table->integer('request_to_location')->index();
            $table->integer('category_id')->nullable();
            $table->integer('sub_category_id')->nullable();
            $table->integer('product_id')->index();
            $table->decimal('qty', 22, 4);
            $table->date('delivery_need_on')->nullable();
            $table->string('status')->default('requested'); // requested, issued, transit, received
            $table->string('notification')->nullable();
            $table->decimal('good_condition', 22, 4)->default(0);
            $table->decimal('damage', 22, 4)->default(0);
            $table->decimal('short', 22, 4)->default(0);
            $table->decimal('expire', 22, 4)->default(0);
            $table->integer('transaction_id')->nullable()->index();
            $table->integer('created_by')->index();
            $table->integer('store_id')->nullable()->index();
            $table->decimal('approved_qty', 22, 4)->default(0);
            $table->integer('from_store')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_requests');
    }
};
