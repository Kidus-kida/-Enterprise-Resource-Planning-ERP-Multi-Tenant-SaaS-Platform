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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // invoice, packing_list, bill_of_lading, etc.
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->unsignedBigInteger('uploaded_by'); // User ID
            $table->datetime('uploaded_at');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
