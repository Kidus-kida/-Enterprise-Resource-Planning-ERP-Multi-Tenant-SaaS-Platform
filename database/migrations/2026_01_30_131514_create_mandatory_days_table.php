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
        Schema::create('mandatory_days', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Year-End Closing", "Inventory Day"
            $table->date('date');
            $table->text('description')->nullable();
            
            // Restrictions
            $table->enum('restriction_type', ['no_leave', 'requires_approval', 'warning_only'])->default('no_leave');
            $table->text('restriction_message')->nullable(); // Custom message to show users
            
            // Scope
            $table->json('applicable_departments')->nullable(); // null = all departments
            $table->json('applicable_designations')->nullable(); // null = all designations
            $table->json('excluded_users')->nullable(); // User IDs who are exempt
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('date');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mandatory_days');
    }
};
