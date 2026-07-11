<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings_audit_logs', function (Blueprint $table) {
            $table->id();

            // The setting key that was changed (dot notation)
            $table->string('key', 191)->index();

            // Values before and after (sensitive values are stored encrypted)
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();

            // Who made the change
            $table->unsignedBigInteger('user_id')->nullable()->index();

            // Request context
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser', 100)->nullable();
            $table->string('device', 50)->nullable();       // desktop|mobile|tablet
            $table->string('request_id', 36)->nullable();   // UUID per HTTP request

            // When it changed (separate column for easy querying, redundant with created_at but explicit)
            $table->timestamp('changed_at')->useCurrent()->index();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings_audit_logs');
    }
};
