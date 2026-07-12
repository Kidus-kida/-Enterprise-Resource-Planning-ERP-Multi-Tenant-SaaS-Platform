<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            
            // Backup file identification
            $table->string('filename')->unique();
            $table->string('path');
            $table->unsignedBigInteger('size'); // Size in bytes
            
            // Backup type: manual, scheduled, pre-restore
            $table->enum('type', ['manual', 'scheduled', 'pre-restore'])->default('manual');
            
            // Backup contents flags
            $table->boolean('includes_database')->default(true);
            $table->boolean('includes_files')->default(true);
            
            // Optional metadata as JSON (can store additional info)
            $table->json('metadata')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at');
            
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index('created_at');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
