<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();

            // Category (e.g. "appearance", "email") — avoids SQL reserved word "group"
            $table->string('category', 50)->index();

            // Optional sub-section within a category (e.g. "colors", "smtp")
            $table->string('section', 100)->nullable()->index();

            // Full dot-notation key: "appearance.primary_color"
            $table->string('key', 191)->unique()->index();

            // The stored value (always a string in database)
            $table->text('value')->nullable();

            // PHP type for casting on read: string|boolean|integer|float|json|image
            $table->string('type', 30)->default('string');

            // UI input widget type
            // text|textarea|select|switch|color|image|password|number|editor|code|email|url|file|range
            $table->string('input_type', 30)->default('text');

            // Human-readable label shown in UI
            $table->string('label', 255)->nullable();

            // Help text / description shown below the input
            $table->text('description')->nullable();

            // JSON array of {label, value} pairs for "select" input_type
            $table->json('options')->nullable();

            // Laravel validation rule string (e.g. "required|email|max:255")
            $table->string('validation_rules', 500)->nullable();

            // Default value — used by restoreDefaults()
            $table->text('default_value')->nullable();

            // Dot-notation key this field depends on (conditional visibility)
            // Format: "key:expected_value" — e.g. "email.driver:smtp"
            $table->string('depends_on', 255)->nullable();

            // Visibility / security flags
            $table->boolean('is_public')->default(false);      // safe for API exposure
            $table->boolean('is_editable')->default(true);     // can be changed at runtime
            $table->boolean('is_sensitive')->default(false);   // encrypt value, mask in UI
            $table->boolean('is_system')->default(false);      // protected core setting

            // Display order within section
            $table->unsignedSmallInteger('sort_order')->default(0);

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
