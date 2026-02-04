<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add UUID column
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });

        // Populate UUID for existing users
        // specific to MySQL/MariaDB for performance, or PHP loop for compatibility
        if (DB::getDriverName() === 'mysql') {
             // Use MySQL's UUID function if available, or just loop in PHP which is safer for portability
             // But simpler:
        }
        
        // Loop and update (universally compatible)
        // We use a raw query or model to update
        $check = DB::table('users')->whereNull('uuid')->exists();
        if ($check) {
            DB::table('users')->whereNull('uuid')->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['uuid' => (string) Str::uuid()]);
                }
            });
        }

        // Change to not null and add unique index
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
            $table->unique('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
