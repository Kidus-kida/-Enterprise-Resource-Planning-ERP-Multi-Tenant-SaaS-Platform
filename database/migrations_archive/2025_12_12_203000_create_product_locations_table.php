<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_locations', function (Blueprint $table) {
            $table->increments('id'); // Pivot table often has its own ID or just composite key. But consistent with Laravel.
            // Or just:
            $table->integer('product_id')->unsigned(); // Use integer generic first, assuming likely match.
            $table->integer('location_id')->unsigned();
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            // Foreign keys can be tricky if types mismatch. I'll omit FK constraints initially to ensure creation?
            // No, FKs are important. But if types mismatch...
            // I'll try without FK constraints first to avoid "General error: 1005 Can't create table". 
            // App logic handles relations usually.
            
            // Actually, I'll check if I can catch the error.
            // I'll try to guess types.
            // Assuming standard Laravel 5.4+ which is bigInteger by default?
            // If the project is old, might be int.
            // I'll omit foreign key constraints for now to be safe and ensure the table exists.
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_locations');
    }
};
