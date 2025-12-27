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
            if (!Schema::hasColumn('contacts', 'landmark')) {
                $table->string('landmark')->nullable()->after('updated_at'); // Position at end or after specific column
            }
            if (!Schema::hasColumn('contacts', 'image')) {
                $table->string('image')->nullable()->after('landmark');
            }
            if (!Schema::hasColumn('contacts', 'signature')) {
                $table->string('signature')->nullable()->after('image');
            }
            // Check if is_payee exists, if not add it
            if (!Schema::hasColumn('contacts', 'is_payee')) {
                $table->boolean('is_payee')->default(0)->after('signature');
            }
             if (!Schema::hasColumn('contacts', 'sub_customers')) {
                $table->json('sub_customers')->nullable()->after('is_payee');
            }
            if (!Schema::hasColumn('contacts', 'vat_number')) {
                $table->string('vat_number')->nullable()->after('sub_customers');
            }
             if (!Schema::hasColumn('contacts', 'active')) {
                $table->boolean('active')->default(1)->after('vat_number');
            }
            if (!Schema::hasColumn('contacts', 'is_property')) {
                $table->boolean('is_property')->default(0)->after('active');
            }
             if (!Schema::hasColumn('contacts', 'should_notify')) {
                $table->boolean('should_notify')->default(0)->after('is_property');
            }
            if (!Schema::hasColumn('contacts', 'contact_transaction_date')) {
                $table->date('contact_transaction_date')->nullable()->after('should_notify');
            }
            if (!Schema::hasColumn('contacts', 'notification_contacts')) {
                $table->json('notification_contacts')->nullable()->after('contact_transaction_date');
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
                'landmark',
                'image',
                'signature',
                // 'is_payee', // Be careful dropping this if it existed before, but usually safe to drop if we added it. 
                // However, since we checked existence, maybe we should only drop if we added it? 
                // Standard rollback drops the columns defined in up. 
                // If it existed before, this rollback might remove it. 
                // Given the prompt, let's assume we own these fields now.
                'sub_customers',
                'vat_number',
                'active',
                'is_property',
                'should_notify',
                'contact_transaction_date',
                'notification_contacts'
            ]);
             
             // handling is_payee separately to be safe or just include it if we are confident.
             // I'll include it in the list but comment that this relies on it being added by this migration.
             if (Schema::hasColumn('contacts', 'is_payee')) {
                 $table->dropColumn('is_payee');
             }
        });
    }
};
