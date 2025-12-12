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
        Schema::create('contact_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->default(1); // Defaulting to 1 as per current logic
            $table->string('type')->index(); // 'character varying' in ERP usually matches string
            $table->string('name');
            $table->decimal('amount', 22, 4)->nullable()->default(0);
            $table->string('price_type')->nullable()->default('percentage'); 
            $table->integer('supplier_group_id')->nullable();
            
            // These might relate to accounting which we might not fully have yet, but adding nullable
            $table->integer('account_type_id')->nullable();
            $table->integer('interest_account_id')->nullable(); 

            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_groups');
    }
};
