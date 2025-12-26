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
            $table->string('name');
            $table->string('account_number')->unique()->nullable();
            $table->unsignedBigInteger('account_type_id');
            $table->unsignedBigInteger('asset_type')->nullable()->comment('Account Group ID');
            $table->unsignedBigInteger('parent_account_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('note')->nullable();
            $table->decimal('opening_balance', 22, 4)->default(0);
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_main_account')->default(false);
            $table->boolean('show_in_balance_sheet')->default(true);
            $table->enum('is_need_cheque', ['Y', 'N'])->default('N');
            $table->boolean('visible')->default(true);
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_type_id')
                ->references('id')
                ->on('account_types')
                ->onDelete('cascade');

            $table->foreign('asset_type')
                ->references('id')
                ->on('account_groups')
                ->onDelete('set null');

            $table->foreign('parent_account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
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
