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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('ast_id');
            $table->string('name');
            $table->date('purchase_date')->nullable();
            $table->string('purchase_from')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_no')->nullable();
            $table->string('supplier')->nullable();
            $table->string('ast_condition')->nullable();
            $table->string('warranty')->nullable();
            $table->string('warranty_end')->nullable();
            $table->string('brand')->nullable();
            $table->string('cost')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('description')->nullable();
            $table->string('status')->nullable()->default('approved');
            $table->longText('files')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->onDelete('cascade');
            $table->foreignId('raised_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->longText('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_issues');
        Schema::dropIfExists('assets');
    }
};
