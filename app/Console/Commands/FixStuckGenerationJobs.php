<?php

namespace App\Console\Commands;

use App\Models\ArticleGenerationJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FixStuckGenerationJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:fix-stuck-jobs {--dry-run : Show what would be fixed without making changes} {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix article generation jobs that are stuck in processing status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('Checking for stuck article generation jobs...');
        
        // Find jobs that have been in 'processing' status for more than 15 minutes
        // or in 'pending' status for more than 30 minutes
        $stuckProcessingJobs = ArticleGenerationJob::where('status', 'processing')
            ->where('started_at', '<=', now()->subMinutes(15))
            ->get();
            
        $stuckPendingJobs = ArticleGenerationJob::where('status', 'pending')
            ->where('created_at', '<=', now()->subMinutes(30))
            ->get();
            
        $totalStuck = $stuckProcessingJobs->count() + $stuckPendingJobs->count();
        
        if ($totalStuck === 0) {
            $this->info('No stuck jobs found!');
            return 0;
        }
        
        $this->warn("Found {$totalStuck} stuck job(s):");
        $this->newLine();
        
        // Display stuck processing jobs
        if ($stuckProcessingJobs->count() > 0) {
            $this->line("Stuck in 'processing' (> 15 minutes):");
            foreach ($stuckProcessingJobs as $job) {
                $duration = $job->started_at ? now()->diffInMinutes($job->started_at) : now()->diffInMinutes($job->created_at);
                $this->line("  - Job #{$job->id}: '{$job->topic}' (stuck for {$duration} minutes)");
            }
            $this->newLine();
        }
        
        // Display stuck pending jobs
        if ($stuckPendingJobs->count() > 0) {
            $this->line("Stuck in 'pending' (> 30 minutes):");
            foreach ($stuckPendingJobs as $job) {
                $duration = now()->diffInMinutes($job->created_at);
                $this->line("  - Job #{$job->id}: '{$job->topic}' (stuck for {$duration} minutes)");
            }
            $this->newLine();
        }
        
        if ($isDryRun) {
            $this->info('DRY RUN - No changes made. Run without --dry-run to fix these jobs.');
            return 0;
        }
        
        // Confirm before proceeding (skip if --force)
        $force = $this->option('force');
        if (!$force && !$this->confirm('Mark these jobs as failed?', true)) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        // Fix stuck processing jobs
        foreach ($stuckProcessingJobs as $job) {
            $duration = $job->started_at ? now()->diffInMinutes($job->started_at) : now()->diffInMinutes($job->created_at);
            $job->markAsFailed("Job timed out after {$duration} minutes. Likely caused by queue worker restart or system error.");
            $this->line("  ✓ Fixed job #{$job->id}");
            
            Log::warning("Fixed stuck processing job", [
                'job_id' => $job->id,
                'topic' => $job->topic,
                'duration_minutes' => $duration
            ]);
        }
        
        // Fix stuck pending jobs
        foreach ($stuckPendingJobs as $job) {
            $duration = now()->diffInMinutes($job->created_at);
            $job->markAsFailed("Job was never picked up by queue worker after {$duration} minutes. Queue may not be running.");
            $this->line("  ✓ Fixed job #{$job->id}");
            
            Log::warning("Fixed stuck pending job", [
                'job_id' => $job->id,
                'topic' => $job->topic,
                'duration_minutes' => $duration
            ]);
        }
        
        $this->newLine();
        $this->info("Successfully fixed {$totalStuck} stuck job(s)!");
        $this->newLine();
        $this->line('Tip: You can schedule this command to run automatically:');
        $this->line('  Add to app/Console/Kernel.php:');
        $this->line('  $schedule->command(\'articles:fix-stuck-jobs\')->everyFifteenMinutes();');
        
        return 0;
    }
}
