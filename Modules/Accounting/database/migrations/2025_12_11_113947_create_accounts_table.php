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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('name');
            $table->string('account_number')->nullable()->unique();
            $table->unsignedBigInteger('account_type_id');
            $table->unsignedBigInteger('asset_type')->nullable(); // Account Group ID
            $table->unsignedBigInteger('parent_account_id')->nullable();
            $table->decimal('opening_balance', 20, 4)->default(0);
            $table->decimal('current_balance', 20, 4)->default(0);
            $table->text('note')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_main_account')->default(0);
            $table->string('is_need_cheque', 1)->default('N'); // Y/N
            $table->boolean('is_closed')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('account_type_id')->references('id')->on('account_types')->onDelete('cascade');
            $table->foreign('asset_type')->references('id')->on('account_groups')->onDelete('set null');
            $table->foreign('parent_account_id')->references('id')->on('accounts')->onDelete('set null');
            
            // Indexes
            $table->index('business_id');
            $table->index('account_type_id');
            $table->index('asset_type');
            $table->index('parent_account_id');
            $table->index('is_closed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
