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
            // GEO (Generative Engine Optimization) Settings - stored as JSON
            $table->json('geo_settings')->nullable()->after('seo_settings');
            
            // GEO settings will include:
            // - structured_data_enhanced: boolean - Enhanced structured data for AI parsing
            // - ai_readability_score: integer - Content optimized for AI comprehension
            // - citation_format: 'apa' | 'mla' | 'chicago' | 'custom' - How to be cited
            // - fact_checking_enabled: boolean - Structured facts for verification
            // - source_attribution: object - Author credentials and expertise
            // - ai_summary: string - AI-friendly content summary
            // - key_facts: array - Structured key facts for AI extraction
            // - entity_markup: array - Clear entity definitions (people, places, things)
            // - conversational_queries: array - Natural language queries this content answers
            // - context_snippets: array - Pre-formatted snippets for AI responses
            // - technical_depth: 'beginner' | 'intermediate' | 'expert'
            // - content_freshness: timestamp - Last verified/updated
            // - api_access_enabled: boolean - Allow AI crawlers API access
            // - llm_training_opt_out: boolean - Opt out of LLM training data
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('geo_settings');
        });
    }
};
