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
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // recipient
            $table->string( 'awarded_by'); // e.g. "HR Department" or "Manager"
            $table->string('title'); // e.g. "Employee of the Month"
            $table->text('description')->nullable();
            $table->string('award_type'); // e.g. "Performance", "Leadership"
            $table->date('awarded_at');
            $table->string('award_file')->nullable(); // optional file path for award certificate
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awards');
    }
};
