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
        Schema::table('articles', function (Blueprint $table) {
            // Master article relationship for variations
            $table->foreignId('master_article_id')->nullable()->constrained('articles')->onDelete('cascade')->after('user_id');
            
            // Generation mode: 'full_ai', 'hybrid_rewrite', 'manual'
            $table->enum('generation_mode', ['full_ai', 'hybrid_rewrite', 'manual'])->default('full_ai')->after('generation_type');
            
            // Variation metadata
            $table->integer('variation_index')->nullable()->after('generation_mode');
            $table->json('variation_metadata')->nullable()->after('variation_index');
            
            // Index for faster queries
            $table->index('master_article_id');
            $table->index('generation_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['master_article_id']);
            $table->dropColumn(['master_article_id', 'generation_mode', 'variation_index', 'variation_metadata']);
        });
    }
};
