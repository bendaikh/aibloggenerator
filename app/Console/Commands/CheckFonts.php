<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckFonts extends Command
{
    protected $signature = 'pinterest:check-fonts';
    protected $description = 'Check which fonts are installed for Pinterest pin generation';

    private array $requiredFonts = [
        'Sans-Serif Fonts' => [
            'arial.ttf' => 'Arial Regular',
            'arialbd.ttf' => 'Arial Bold',
            'ariblk.ttf' => 'Arial Black',
            'Montserrat-Bold.ttf' => 'Montserrat Bold',
            'Montserrat-Regular.ttf' => 'Montserrat Regular',
            'BebasNeue-Regular.ttf' => 'Bebas Neue',
            'Poppins-Bold.ttf' => 'Poppins Bold',
            'Poppins-Regular.ttf' => 'Poppins Regular',
            'Roboto-Bold.ttf' => 'Roboto Bold',
            'Roboto-Regular.ttf' => 'Roboto Regular',
            'OpenSans-Bold.ttf' => 'Open Sans Bold',
            'OpenSans-Regular.ttf' => 'Open Sans Regular',
        ],
        'Serif Fonts' => [
            'georgia.ttf' => 'Georgia Regular',
            'georgiab.ttf' => 'Georgia Bold',
            'georgiai.ttf' => 'Georgia Italic',
            'times.ttf' => 'Times New Roman Regular',
            'timesbd.ttf' => 'Times New Roman Bold',
            'timesi.ttf' => 'Times New Roman Italic',
            'PlayfairDisplay-Bold.ttf' => 'Playfair Display Bold',
            'PlayfairDisplay-Regular.ttf' => 'Playfair Display Regular',
        ],
        'Script/Decorative Fonts' => [
            'GreatVibes-Regular.ttf' => 'Great Vibes',
            'DancingScript-Bold.ttf' => 'Dancing Script Bold',
            'DancingScript-Regular.ttf' => 'Dancing Script Regular',
            'Pacifico-Regular.ttf' => 'Pacifico',
        ]
    ];

    public function handle(): int
    {
        $fontsDir = resource_path('fonts');
        
        if (!File::isDirectory($fontsDir)) {
            $this->error("Fonts directory not found: {$fontsDir}");
            $this->comment('Run: php artisan pinterest:install-fonts');
            return self::FAILURE;
        }

        $this->info('Checking installed fonts for Pinterest pins...');
        $this->newLine();

        $totalRequired = 0;
        $totalInstalled = 0;

        foreach ($this->requiredFonts as $category => $fonts) {
            $this->line("<fg=cyan;options=bold>{$category}</>");
            $rows = [];

            foreach ($fonts as $filename => $displayName) {
                $totalRequired++;
                $filePath = $fontsDir . '/' . $filename;
                $exists = File::exists($filePath);
                
                if ($exists) {
                    $totalInstalled++;
                    $fileSize = File::size($filePath);
                    $rows[] = [
                        '✓',
                        "<fg=green>{$displayName}</>",
                        $this->formatBytes($fileSize),
                        "<fg=gray>{$filename}</>"
                    ];
                } else {
                    $rows[] = [
                        '✗',
                        "<fg=red>{$displayName}</>",
                        'Missing',
                        "<fg=gray>{$filename}</>"
                    ];
                }
            }

            $this->table(['', 'Font Name', 'Size', 'File'], $rows);
            $this->newLine();
        }

        // Summary
        $percentage = round(($totalInstalled / $totalRequired) * 100, 1);
        $this->info("Summary: {$totalInstalled}/{$totalRequired} fonts installed ({$percentage}%)");
        
        if ($totalInstalled < $totalRequired) {
            $this->newLine();
            $this->warn('⚠ Some fonts are missing!');
            $this->comment('Run the following command to install missing fonts:');
            $this->line('  php artisan pinterest:install-fonts');
            return self::FAILURE;
        } else {
            $this->newLine();
            $this->info('✓ All required fonts are installed!');
            return self::SUCCESS;
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
