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
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id(); // Primary key

            $table->string('log_name')->nullable(); // Name of the log
            $table->string('description'); // Description of the activity
            $table->string('event'); // Event type: created, updated, deleted, etc.

            // Polymorphic relation to the subject (the model affected)
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_type')->nullable();

            // Polymorphic relation to the causer (who caused the activity)
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('causer_type')->nullable();

            $table->json('properties')->nullable(); // Extra properties for the log
            $table->uuid('batch_uuid')->nullable(); // Custom field

            $table->timestamps(); // created_at and updated_at

            // Optional index for performance
            $table->index(['subject_id', 'subject_type']);
            $table->index(['causer_id', 'causer_type']);
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
