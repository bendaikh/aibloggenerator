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
        Schema::create('pinterest_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->foreignId('article_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Pin content
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('link')->nullable(); // Link to the article
            
            // Design elements
            $table->string('top_image')->nullable(); // First image path
            $table->string('bottom_image')->nullable(); // Second image path
            $table->string('generated_image')->nullable(); // Final combined Pinterest image
            
            // Text overlay settings
            $table->string('headline_text')->nullable(); // Main text (e.g., "cozy cinnamon")
            $table->string('subheadline_text')->nullable(); // Secondary text (e.g., "Sugar donut bread")
            $table->string('headline_font')->default('sans-serif');
            $table->string('subheadline_font')->default('script');
            $table->string('headline_color')->default('#ffffff');
            $table->string('subheadline_color')->default('#d4a574');
            $table->string('overlay_color')->default('#000000');
            $table->integer('overlay_opacity')->default(70); // 0-100
            
            // Status
            $table->enum('status', ['pending', 'generated', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['website_id', 'status']);
            $table->index('article_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinterest_pins');
    }
};
