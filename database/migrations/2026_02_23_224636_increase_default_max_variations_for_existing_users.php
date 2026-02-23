<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration updates the max_variations default to 25 for all users
     * who currently have the old default of 5. This ensures users with many
     * websites can generate articles for all of them in hybrid mode.
     */
    public function up(): void
    {
        // Update existing users who have the old default of 5
        DB::table('users')
            ->where('max_variations', 5)
            ->update(['max_variations' => 25]);
        
        // Also update the column default for new users
        Schema::table('users', function (Blueprint $table) {
            $table->integer('max_variations')->default(25)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to the old default
        Schema::table('users', function (Blueprint $table) {
            $table->integer('max_variations')->default(5)->change();
        });
    }
};
