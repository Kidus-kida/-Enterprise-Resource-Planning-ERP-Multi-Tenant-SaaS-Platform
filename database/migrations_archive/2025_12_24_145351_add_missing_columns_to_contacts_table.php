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
        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'is_payee')) {
                if (Schema::hasColumn('contacts', 'signature')) {
                    $table->boolean('is_payee')->default(0)->after('signature');
                } else {
                    $table->boolean('is_payee')->default(0);
                }
            }

            if (!Schema::hasColumn('contacts', 'contact_status')) {
                if (Schema::hasColumn('contacts', 'is_payee')) {
                    $table->string('contact_status')->default('active')->after('is_payee');
                } else {
                    $table->string('contact_status')->default('active');
                }
            }

            if (!Schema::hasColumn('contacts', 'address_2')) {
                if (Schema::hasColumn('contacts', 'address')) {
                    $table->text('address_2')->nullable()->after('address');
                } else {
                    $table->text('address_2')->nullable();
                }
            }

            if (!Schema::hasColumn('contacts', 'address_3')) {
                if (Schema::hasColumn('contacts', 'address_2')) {
                    $table->text('address_3')->nullable()->after('address_2');
                } else {
                    $table->text('address_3')->nullable();
                }
            }

            if (!Schema::hasColumn('contacts', 'geo_location')) {
                if (Schema::hasColumn('contacts', 'address_3')) {
                    $table->string('geo_location')->nullable()->after('address_3');
                } else {
                    $table->string('geo_location')->nullable();
                }
            }

            if (!Schema::hasColumn('contacts', 'nic_number')) {
                if (Schema::hasColumn('contacts', 'geo_location')) {
                    $table->string('nic_number', 20)->nullable()->after('geo_location');
                } else {
                    $table->string('nic_number', 20)->nullable();
                }
            }

            if (!Schema::hasColumn('contacts', 'user_id')) {
                if (Schema::hasColumn('contacts', 'nic_number')) {
                    $table->integer('user_id')->nullable()->after('nic_number');
                } else {
                    $table->integer('user_id')->nullable();
                }
            }

            if (!Schema::hasColumn('contacts', 'sub_customer')) {
                if (Schema::hasColumn('contacts', 'user_id')) {
                    $table->integer('sub_customer')->default(0)->after('user_id');
                } else {
                    $table->integer('sub_customer')->default(0);
                }
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
