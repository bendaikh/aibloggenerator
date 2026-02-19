<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Theme;

class SetDefaultThemesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the Recipe theme (default for all users)
        $recipeTheme = Theme::where('slug', 'recipe')->first();

        if ($recipeTheme) {
            // Set all users without a theme to use the Recipe theme
            User::whereNull('theme_id')->update(['theme_id' => $recipeTheme->id]);
            
            $this->command->info('Default themes set successfully for all users!');
        } else {
            $this->command->error('Recipe theme not found. Please run migrations first.');
        }
    }
}
