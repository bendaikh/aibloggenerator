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
        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider')->default('openai'); // openai, anthropic, etc.
            $table->string('model')->nullable(); // gpt-4o, gpt-4-turbo, etc.
            $table->string('operation')->default('article_generation'); // article_generation, etc.
            $table->integer('prompt_tokens')->default(0);
            $table->integer('completion_tokens')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->decimal('estimated_cost', 10, 6)->default(0); // Cost in USD
            $table->string('generation_mode')->default('full_ai'); // full_ai, hybrid_rewrite
            $table->foreignId('article_id')->nullable()->constrained()->onDelete('set null');
            $table->text('metadata')->nullable(); // JSON metadata
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('provider');
            $table->index('generation_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_usage_logs');
    }
};
