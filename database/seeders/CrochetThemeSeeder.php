<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CrochetThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('themes')->updateOrInsert(
            ['slug' => 'crochet'],
            [
                'name' => 'Crochet Theme',
                'slug' => 'crochet',
                'description' => 'Playful and cozy theme for crochet patterns and stitch guides with a soft green and pastel color palette',
                'show_recipe_sections' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        echo "✅ Crochet theme has been seeded!\n";
    }
}
