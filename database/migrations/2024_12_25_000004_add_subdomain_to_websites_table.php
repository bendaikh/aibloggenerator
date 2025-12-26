<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Website;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First add the column without unique constraint
        Schema::table('websites', function (Blueprint $table) {
            $table->string('subdomain')->nullable()->after('slug');
        });

        // Update existing websites to have subdomain = slug
        Website::whereNull('subdomain')->orWhere('subdomain', '')->each(function ($website) {
            $website->update(['subdomain' => $website->slug]);
        });

        // Now add the unique constraint
        Schema::table('websites', function (Blueprint $table) {
            $table->unique('subdomain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('subdomain');
        });
    }
};

