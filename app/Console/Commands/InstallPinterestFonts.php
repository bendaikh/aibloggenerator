<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class InstallPinterestFonts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pinterest:install-fonts {--force : Force download even if fonts exist}';

    /**
     * The console command description.
     */
    protected $description = 'Download and install required fonts for Pinterest pin generation';

    /**
     * Font download configuration
     * Using Google Fonts repository (google/fonts) for reliable downloads
     */
    private array $fonts = [
        // Montserrat - Modern geometric sans-serif
        'Montserrat-Bold.ttf' => 'https://github.com/google/fonts/raw/main/ofl/montserrat/static/Montserrat-Bold.ttf',
        'Montserrat-Regular.ttf' => 'https://github.com/google/fonts/raw/main/ofl/montserrat/static/Montserrat-Regular.ttf',
        
        // Bebas Neue - Bold display font
        'BebasNeue-Regular.ttf' => 'https://github.com/google/fonts/raw/main/ofl/bebasneue/BebasNeue-Regular.ttf',
        
        // Poppins - Geometric sans-serif
        'Poppins-Bold.ttf' => 'https://github.com/google/fonts/raw/main/ofl/poppins/Poppins-Bold.ttf',
        'Poppins-Regular.ttf' => 'https://github.com/google/fonts/raw/main/ofl/poppins/Poppins-Regular.ttf',
        
        // Playfair Display - High-contrast serif  
        'PlayfairDisplay-Bold.ttf' => 'https://github.com/google/fonts/raw/main/ofl/playfairdisplay/PlayfairDisplay%5Bopsz%2Cwght%5D.ttf',
        'PlayfairDisplay-Regular.ttf' => 'https://github.com/google/fonts/raw/main/ofl/playfairdisplay/PlayfairDisplay-Italic%5Bopsz%2Cwght%5D.ttf',
        
        // Great Vibes - Elegant script
        'GreatVibes-Regular.ttf' => 'https://github.com/google/fonts/raw/main/ofl/greatvibes/GreatVibes-Regular.ttf',
        
        // Roboto - Modern sans-serif
        'Roboto-Bold.ttf' => 'https://github.com/google/fonts/raw/main/apache/roboto/static/Roboto-Bold.ttf',
        'Roboto-Regular.ttf' => 'https://github.com/google/fonts/raw/main/apache/roboto/static/Roboto-Regular.ttf',
        
        // Open Sans - Humanist sans-serif
        'OpenSans-Bold.ttf' => 'https://github.com/google/fonts/raw/main/apache/opensans/static/OpenSans-Bold.ttf',
        'OpenSans-Regular.ttf' => 'https://github.com/google/fonts/raw/main/apache/opensans/static/OpenSans-Regular.ttf',
        
        // Dancing Script - Casual script (using variable font for both)
        'DancingScript-Bold.ttf' => 'https://github.com/google/fonts/raw/main/ofl/dancingscript/DancingScript%5Bwght%5D.ttf',
        'DancingScript-Regular.ttf' => 'https://github.com/google/fonts/raw/main/ofl/dancingscript/DancingScript%5Bwght%5D.ttf',
        
        // Pacifico - Fun surf-style script
        'Pacifico-Regular.ttf' => 'https://github.com/google/fonts/raw/main/ofl/pacifico/Pacifico-Regular.ttf',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fontsDir = resource_path('fonts');
        
        // Create fonts directory if it doesn't exist
        if (!File::isDirectory($fontsDir)) {
            File::makeDirectory($fontsDir, 0755, true);
            $this->info("✓ Created fonts directory: {$fontsDir}");
        }

        $this->info('Starting font installation for Pinterest pins...');
        $this->newLine();

        $downloaded = 0;
        $skipped = 0;
        $failed = 0;

        $progressBar = $this->output->createProgressBar(count($this->fonts));
        $progressBar->start();

        foreach ($this->fonts as $filename => $url) {
            $filePath = $fontsDir . '/' . $filename;
            
            // Skip if file exists and not forcing
            if (File::exists($filePath) && !$this->option('force')) {
                $skipped++;
                $progressBar->advance();
                continue;
            }

            try {
                // Download font file
                $response = Http::timeout(30)->get($url);
                
                if ($response->successful()) {
                    File::put($filePath, $response->body());
                    $downloaded++;
                } else {
                    $this->newLine();
                    $this->warn("Failed to download {$filename}: HTTP {$response->status()}");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Error downloading {$filename}: {$e->getMessage()}");
                $failed++;
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Font Installation Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['Downloaded', $downloaded],
                ['Skipped (already exist)', $skipped],
                ['Failed', $failed],
                ['Total', count($this->fonts)],
            ]
        );

        if ($downloaded > 0) {
            $this->newLine();
            $this->info("✓ Successfully installed {$downloaded} font(s)!");
        }

        if ($skipped > 0) {
            $this->comment("ℹ {$skipped} font(s) already existed (use --force to re-download)");
        }

        if ($failed > 0) {
            $this->newLine();
            $this->error("✗ {$failed} font(s) failed to download");
            $this->comment('Please download these fonts manually from:');
            $this->comment('https://fonts.google.com');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('You can now use all fonts in Pinterest pin designs!');
        
        return self::SUCCESS;
    }
}
