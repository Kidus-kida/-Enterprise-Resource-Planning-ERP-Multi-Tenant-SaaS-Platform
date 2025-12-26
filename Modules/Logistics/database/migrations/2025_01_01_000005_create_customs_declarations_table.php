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
        Schema::create('customs_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('declaration_no')->unique();
            $table->foreignId('hs_code_id')->constrained('hs_codes');
            
            $table->decimal('tariff_rate', 5, 2);
            $table->decimal('cif_value_usd', 15, 2);
            $table->decimal('exchange_rate', 10, 4);
            $table->decimal('cif_value_etb', 15, 2);
            
            $table->decimal('import_duty', 15, 2)->default(0);
            $table->decimal('vat', 15, 2)->default(0);
            $table->decimal('surtax', 15, 2)->default(0);
            $table->decimal('excise', 15, 2)->default(0);
            $table->decimal('withholding', 15, 2)->default(0);
            $table->decimal('customs_service_fee', 15, 2)->default(0);
            $table->decimal('total_duties', 15, 2)->default(0);
            
            $table->enum('risk_channel', ['green', 'yellow', 'red', 'blue'])->default('yellow');
            $table->date('declaration_date');
            $table->date('clearance_date')->nullable();
            
            $table->enum('status', ['pending_docs', 'assessment', 'under_review', 'duty_payment', 'cleared', 'rejected'])->default('pending_docs');
            $table->integer('progress')->default(0); // 0-100
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customs_declarations');
    }
};
