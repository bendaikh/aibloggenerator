<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('show_recipe_sections')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default themes
        DB::table('themes')->insert([
            [
                'name' => 'Recipe Theme',
                'slug' => 'recipe',
                'description' => 'Full recipe theme with ingredients and instructions cards',
                'show_recipe_sections' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Home Decor Theme',
                'slug' => 'home-decor',
                'description' => 'Clean theme for home decor content without recipe sections',
                'show_recipe_sections' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
