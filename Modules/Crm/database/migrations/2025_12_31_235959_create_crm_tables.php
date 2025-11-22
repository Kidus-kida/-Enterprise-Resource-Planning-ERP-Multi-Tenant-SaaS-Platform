<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Leads table - ONLY created_by
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Campaigns table - ONLY status
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
            $table->timestamps();
        });

        // Follow-ups table - ONLY assigned_to & created_by
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('follow_ups');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('leads');
    }
};
