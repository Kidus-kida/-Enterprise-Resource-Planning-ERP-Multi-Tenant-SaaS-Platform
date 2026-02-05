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
        Schema::create('package_addons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 22, 4)->default(0);
            $table->string('module_key')->unique();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(1);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->foreignId('module_id')->nullable()
                ->constrained('modules')->onDelete('set null');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_addons');
    }
};
