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
        Schema::create('article_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('prompt')->nullable();
            $table->text('revised_prompt')->nullable();
            $table->string('original_url')->nullable();
            $table->string('local_path');
            $table->integer('position')->default(0);
            $table->string('size')->default('1024x1024');
            $table->string('quality')->default('standard');
            $table->string('style')->default('natural');
            $table->decimal('cost', 10, 6)->default(0);
            $table->enum('generation_type', ['ai', 'uploaded'])->default('ai');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['article_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_images');
    }
};
