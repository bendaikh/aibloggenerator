<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckFonts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pinterest:check-fonts';

    /**
     * The console command description.
     */
    protected $description = 'Check which fonts are available for Pinterest pin generation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking font availability for Pinterest pins...');
        $this->newLine();

        // Windows fonts directory
        $winFonts = 'C:\\Windows\\Fonts';
        
        // Resource fonts directory
        $fontsDir = resource_path('fonts');
        
        $this->info("Resource fonts directory: {$fontsDir}");
        $this->info("Windows fonts directory: {$winFonts}");
        $this->newLine();

        // Check if directories exist
        $this->line("Resource fonts dir exists: " . (is_dir($fontsDir) ? '✓ Yes' : '✗ No'));
        $this->line("Windows fonts dir exists: " . (is_dir($winFonts) ? '✓ Yes' : '✗ No'));
        $this->newLine();

        // Check specific fonts
        $fontsToCheck = [
            'arial' => [
                $winFonts . '\\arialbd.ttf',
                $winFonts . '\\arial.ttf',
                $winFonts . '\\ARIALBD.TTF',
                $winFonts . '\\ARIAL.TTF',
            ],
            'georgia' => [
                $winFonts . '\\georgia.ttf',
                $winFonts . '\\georgiab.ttf',
                $winFonts . '\\GEORGIA.TTF',
            ],
            'times' => [
                $winFonts . '\\times.ttf',
                $winFonts . '\\timesbd.ttf',
                $winFonts . '\\TIMES.TTF',
            ],
            'segoe' => [
                $winFonts . '\\segoeuib.ttf',
                $winFonts . '\\segoeui.ttf',
                $winFonts . '\\SEGOEUIB.TTF',
            ],
        ];

        $this->table(
            ['Font Family', 'Path', 'Available'],
            collect($fontsToCheck)->flatMap(function ($paths, $family) {
                return collect($paths)->map(function ($path) use ($family) {
                    return [
                        $family,
                        $path,
                        file_exists($path) ? '✓ Yes' : '✗ No'
                    ];
                });
            })->toArray()
        );

        $this->newLine();

        // List actual files in Windows Fonts directory (first 20)
        if (is_dir($winFonts)) {
            $this->info('Sample of fonts in Windows Fonts directory:');
            $files = scandir($winFonts);
            $ttfFiles = array_filter($files, fn($f) => str_ends_with(strtolower($f), '.ttf'));
            $ttfFiles = array_slice($ttfFiles, 0, 20);
            foreach ($ttfFiles as $file) {
                $this->line("  - {$file}");
            }
        }

        $this->newLine();

        // Test font loading
        $this->info('Testing TTF font loading with GD...');
        $testFont = null;
        foreach ($fontsToCheck['arial'] as $path) {
            if (file_exists($path)) {
                $testFont = $path;
                break;
            }
        }

        if ($testFont) {
            $this->line("Testing with font: {$testFont}");
            if (function_exists('imagettfbbox')) {
                $bbox = @imagettfbbox(24, 0, $testFont, 'Test');
                if ($bbox !== false) {
                    $this->info('✓ Font loaded successfully with GD!');
                } else {
                    $this->error('✗ Font file exists but GD could not load it');
                }
            } else {
                $this->error('✗ imagettfbbox function not available (GD not installed properly)');
            }
        } else {
            $this->error('✗ No test font found');
        }

        return Command::SUCCESS;
    }
}
