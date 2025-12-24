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
        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'is_payee')) {
                $table->boolean('is_payee')->default(0)->after('signature');
            }
            if (!Schema::hasColumn('contacts', 'contact_status')) {
                $table->string('contact_status')->default('active')->after('is_payee');
            }
            if (!Schema::hasColumn('contacts', 'address_2')) {
                $table->text('address_2')->nullable()->after('address');
            }
            if (!Schema::hasColumn('contacts', 'address_3')) {
                $table->text('address_3')->nullable()->after('address_2');
            }
            if (!Schema::hasColumn('contacts', 'geo_location')) {
                $table->string('geo_location')->nullable()->after('address_3');
            }
            if (!Schema::hasColumn('contacts', 'nic_number')) {
                $table->string('nic_number', 20)->nullable()->after('geo_location');
            }
            if (!Schema::hasColumn('contacts', 'user_id')) {
                $table->integer('user_id')->nullable()->after('nic_number');
            }
            if (!Schema::hasColumn('contacts', 'sub_customer')) {
                $table->integer('sub_customer')->default(0)->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'is_payee',
                'contact_status',
                'address_2',
                'address_3',
                'geo_location',
                'nic_number',
                'user_id',
                'sub_customer'
            ]);
        });
    }
};
