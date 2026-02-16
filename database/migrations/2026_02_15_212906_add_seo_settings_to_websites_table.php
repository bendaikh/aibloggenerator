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
            // SEO Settings JSON column for comprehensive SEO configuration
            $table->json('seo_settings')->nullable()->after('theme_settings');
            
            // Google Search Console verification code
            $table->string('google_verification', 100)->nullable()->after('seo_settings');
            
            // Bing Webmaster Tools verification code
            $table->string('bing_verification', 100)->nullable()->after('google_verification');
            
            // Yandex verification code
            $table->string('yandex_verification', 100)->nullable()->after('bing_verification');
            
            // Custom robots.txt content
            $table->text('robots_txt')->nullable()->after('yandex_verification');
            
            // Google Analytics ID (GA4)
            $table->string('google_analytics_id', 50)->nullable()->after('robots_txt');
            
            // Google Tag Manager ID
            $table->string('gtm_id', 50)->nullable()->after('google_analytics_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn([
                'seo_settings',
                'google_verification',
                'bing_verification',
                'yandex_verification',
                'robots_txt',
                'google_analytics_id',
                'gtm_id',
            ]);
        });
    }
};
