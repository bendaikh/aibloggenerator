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
        Schema::table('users', function (Blueprint $table) {
            // Article generation mode preference
            $table->enum('article_generation_mode', ['full_ai', 'hybrid_rewrite'])->default('full_ai')->after('ai_model');
            
            // Number of variations to generate in hybrid mode
            $table->integer('max_variations')->default(5)->after('article_generation_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['article_generation_mode', 'max_variations']);
        });
    }
};
