<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ClearAllCaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all application caches (config, route, view, cache, compiled)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Clearing all caches...');

        // Clear application cache
        Artisan::call('cache:clear');
        $this->info('✓ Application cache cleared');

        // Clear route cache
        Artisan::call('route:clear');
        $this->info('✓ Route cache cleared');

        // Clear config cache
        Artisan::call('config:clear');
        $this->info('✓ Config cache cleared');

        // Clear view cache
        Artisan::call('view:clear');
        $this->info('✓ View cache cleared');

        // Clear compiled class cache
        Artisan::call('clear-compiled');
        $this->info('✓ Compiled classes cleared');

        // Clear event cache (Laravel 8+)
        try {
            Artisan::call('event:clear');
            $this->info('✓ Event cache cleared');
        } catch (\Exception $e) {
            // Event cache might not be available in all Laravel versions
        }

        $this->newLine();
        $this->info('All caches have been cleared successfully!');
        $this->comment('You may need to rebuild caches with: php artisan optimize');

        return Command::SUCCESS;
    }
}
