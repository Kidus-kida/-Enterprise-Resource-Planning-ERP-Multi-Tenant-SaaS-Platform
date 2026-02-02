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
        Schema::table('businesses', function (Blueprint $table) {
            // Owner contact information (for initial onboarding)
            $table->string('owner_email')->nullable()->after('owner_id');
            $table->string('owner_firstname')->nullable()->after('owner_email');
            $table->string('owner_lastname')->nullable()->after('owner_firstname');
            $table->string('owner_phone')->nullable()->after('owner_lastname');
            
            // Link to tenant user after creation (canonical reference)
            $table->uuid('owner_user_uuid')->nullable()->after('owner_phone');
            
            // Invite tracking
            $table->timestamp('owner_invite_sent_at')->nullable()->after('owner_user_uuid');
            $table->timestamp('owner_activated_at')->nullable()->after('owner_invite_sent_at');
            
            // Indexes for performance
            $table->index('owner_email');
            $table->index('owner_user_uuid');
        });
        
        // Make owner_id nullable for new businesses (existing ones keep their owner_id)
        Schema::table('businesses', function (Blueprint $table) {
            $table->unsignedInteger('owner_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropIndex(['owner_email']);
            $table->dropIndex(['owner_user_uuid']);
            
            $table->dropColumn([
                'owner_email',
                'owner_firstname',
                'owner_lastname',
                'owner_phone',
                'owner_user_uuid',
                'owner_invite_sent_at',
                'owner_activated_at'
            ]);
        });
        
        // Restore owner_id to NOT NULL (only if safe)
        // Note: This may fail if there are businesses with NULL owner_id
        // Schema::table('businesses', function (Blueprint $table) {
        //     $table->unsignedInteger('owner_id')->nullable(false)->change();
        // });
    }
};
