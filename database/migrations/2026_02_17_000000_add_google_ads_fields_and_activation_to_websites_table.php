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
        Schema::table('websites', function (Blueprint $table) {
            // Add Google AdSense fields
            $table->string('google_adsense_id')->nullable()->after('ads_txt');
            $table->json('google_ads_placements')->nullable()->after('google_adsense_id');
            
            // Add activation flags - only one can be active at a time
            $table->boolean('hbagency_active')->default(false)->after('hbagency_placements');
            $table->boolean('google_ads_active')->default(false)->after('google_ads_placements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn([
                'google_adsense_id',
                'google_ads_placements',
                'hbagency_active',
                'google_ads_active'
            ]);
        });
    }
};
