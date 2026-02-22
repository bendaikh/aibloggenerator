# Deployment Guide: Duplicate Job Fixes

## ⚠️ IMPORTANT: Deployment Steps

These fixes require **restarting your queue workers** because the job class has been modified.

### Step 1: Clear Existing Queue (Production)

```bash
# SSH into your production server

# 1. Stop all queue workers
php artisan queue:restart

# 2. Clear the cache (releases job uniqueness locks)
php artisan cache:clear

# 3. (Optional) Clear failed jobs if any
php artisan queue:flush

# 4. Check if any jobs are stuck in "processing" status
php artisan tinker
>>> ArticleGenerationJob::where('status', 'processing')->count()
>>> # If there are stuck jobs, you can reset them:
>>> ArticleGenerationJob::where('status', 'processing')->where('started_at', '<', now()->subHours(1))->update(['status' => 'failed', 'error_message' => 'Timeout - reset by admin'])
>>> exit
```

### Step 2: Deploy Code Changes

```bash
# Pull the latest code (Git)
git pull origin main  # or your branch name

# If using Composer for dependencies
composer install --no-dev --optimize-autoloader

# Clear Laravel caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimize for production (optional)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 3: Restart Queue Workers

```bash
# Restart the queue worker (picks up new job class)
php artisan queue:restart

# If using Supervisor, restart it
sudo supervisorctl restart all
# OR for specific program:
sudo supervisorctl restart laravel-worker:*

# If using systemd
sudo systemctl restart laravel-worker
```

### Step 4: Verify Workers Are Running

```bash
# Check queue worker status
php artisan queue:work --once --verbose

# If using Supervisor
sudo supervisorctl status

# Check recent logs
tail -f storage/logs/laravel.log
```

## For Development Environment

```bash
# Stop the queue worker (Ctrl+C)

# Clear cache
php artisan cache:clear

# Restart queue worker
php artisan queue:work --verbose
```

## Testing After Deployment

### Test 1: Single Article Generation
1. Go to Global AI Articles
2. Select 1-3 websites
3. Enter a topic: "Test Article Generation $(date +%s)"
4. Click "Generate"
5. **Expected**: Jobs show "Processing" → "Completed"
6. **Not Expected**: Jobs going back to "Processing" after "Completed"

### Test 2: Duplicate Prevention
1. Generate an article with topic "Duplicate Test"
2. Immediately try to generate again with same topic
3. **Expected**: Error message "A job for this topic is already being processed"

### Test 3: Queue Worker Logs
```bash
tail -f storage/logs/laravel.log | grep -i "already completed"
```
You should see logs like:
- "All jobs already completed, aborting"
- "Job {id} is already completed, skipping"

## Troubleshooting

### Issue: Jobs still getting reprocessed

**Solution 1**: Clear cache and restart workers
```bash
php artisan cache:clear
php artisan queue:restart
```

**Solution 2**: Check if multiple queue workers are running
```bash
ps aux | grep "queue:work"
# Kill any duplicate workers
```

**Solution 3**: Check database queue table
```bash
php artisan tinker
>>> DB::table('jobs')->count()
>>> # If there are duplicate jobs, clear them:
>>> DB::table('jobs')->delete()
```

### Issue: Jobs stuck in "pending" status

**Solution**: Queue worker not running
```bash
# Check if worker is running
ps aux | grep "queue:work"

# Start worker if not running
php artisan queue:work --daemon
```

### Issue: "Class not found" error after deployment

**Solution**: Autoloader not updated
```bash
composer dump-autoload
php artisan optimize:clear
php artisan queue:restart
```

## Production Monitoring

Add to your monitoring/alerting:

```bash
# Check for stuck jobs (processing > 1 hour)
php artisan tinker
>>> $stuck = ArticleGenerationJob::where('status', 'processing')
>>>     ->where('started_at', '<', now()->subHour())
>>>     ->count();
>>> echo "Stuck jobs: $stuck\n";
```

Consider setting up a cron job to auto-reset stuck jobs:

```bash
# In crontab
0 * * * * cd /path/to/your/app && php artisan schedule:run
```

Then in `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    // Reset stuck jobs every hour
    $schedule->call(function () {
        ArticleGenerationJob::where('status', 'processing')
            ->where('started_at', '<', now()->subHour())
            ->update([
                'status' => 'failed',
                'error_message' => 'Job timeout - automatically reset',
                'completed_at' => now()
            ]);
    })->hourly();
}
```

## Performance Notes

- The `ShouldBeUnique` interface uses cache to track unique jobs
- For better performance, consider using Redis instead of database cache:
  ```
  CACHE_STORE=redis
  ```
- The `uniqueFor` is set to 1 hour (3600 seconds), adjust if needed
- Database row locking (`lockForUpdate()`) may cause slight performance impact under heavy load but prevents race conditions

## Rollback Plan

If issues occur after deployment:

```bash
# 1. Stop queue workers
php artisan queue:restart

# 2. Rollback code
git checkout previous-commit-hash

# 3. Clear caches
php artisan cache:clear
php artisan config:clear

# 4. Restart workers
php artisan queue:work
```

## Support

If you continue to see duplicate processing:
1. Check `storage/logs/laravel.log` for errors
2. Verify cache driver is working: `php artisan tinker` → `Cache::put('test', 'value', 60)` → `Cache::get('test')`
3. Check database for duplicate jobs: `SELECT * FROM jobs WHERE queue = 'default'`
4. Ensure only ONE queue worker is running per queue
