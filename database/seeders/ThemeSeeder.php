<?php

use Illuminate\Support\Facades\DB;

// This seeder helps set up a test website with the home decor theme

DB::table('themes')->updateOrInsert(
    ['slug' => 'home-decor'],
    [
        'name' => 'Home Decor Theme',
        'slug' => 'home-decor',
        'description' => 'Warm, bohemian-inspired theme for home decor blogs with beige/cream color palette',
        'show_recipe_sections' => false,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]
);

DB::table('themes')->updateOrInsert(
    ['slug' => 'recipe'],
    [
        'name' => 'Recipe Theme',
        'slug' => 'recipe',
        'description' => 'Full recipe theme with ingredients and instructions cards',
        'show_recipe_sections' => true,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]
);

    DB::table('themes')->updateOrInsert(
        ['slug' => 'crochet'],
        [
            'name' => 'Crochet Theme',
            'slug' => 'crochet',
            'description' => 'Cozy, modern crochet theme with soft greens and creative layouts',
            'show_recipe_sections' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    echo "✅ Themes have been seeded!\n";
    echo "\nAvailable themes:\n";
    echo "  1. Recipe Theme (slug: recipe) - Green/Emerald colors\n";
    echo "  2. Home Decor Theme (slug: home-decor) - Beige/Terracotta colors\n";
    echo "  3. Crochet Theme (slug: crochet) - Soft Green/Hook Vibes colors\n";
    echo "\nTo apply the crochet theme to a website, run:\n";
    echo "  php artisan tinker\n";
    echo "  \$website = \\App\\Models\\Website::find(YOUR_WEBSITE_ID);\n";
    echo "  \$theme = \\App\\Models\\Theme::where('slug', 'crochet')->first();\n";
    echo "  \$website->theme_id = \$theme->id;\n";
    echo "  \$website->save();\n";
