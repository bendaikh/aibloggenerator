# Quick Reference: Article Generation Job Management

## Commands

### Check for Stuck Jobs (Dry Run)
```bash
php artisan articles:fix-stuck-jobs --dry-run
```
Shows what would be fixed without making changes.

### Fix Stuck Jobs (With Confirmation)
```bash
php artisan articles:fix-stuck-jobs
```
Prompts for confirmation before fixing.

### Fix Stuck Jobs (No Confirmation)
```bash
php artisan articles:fix-stuck-jobs --force
```
Fixes immediately without prompting. Used by scheduler.

## Automatic Cleanup

The system automatically runs `articles:fix-stuck-jobs --force` every 15 minutes.

**To enable the scheduler:**
```bash
# Linux/Mac - Add to crontab
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

# Windows - Use Task Scheduler or run manually
php artisan schedule:run
```

## What Gets Fixed

| Status | Threshold | Action |
|--------|-----------|--------|
| `processing` | > 15 minutes | Marked as failed |
| `pending` | > 30 minutes | Marked as failed |
| `completed` | - | No action |
| `failed` | - | No action |

## Job Status Flow

```
pending → processing → completed ✅
    ↓         ↓
  failed    failed ❌
```

## Monitoring

### View Recent Jobs
```bash
php artisan tinker
> ArticleGenerationJob::recent()->get();
```

### Count Active Jobs
```bash
php artisan tinker
> ArticleGenerationJob::active()->count();
```

### Check Failed Jobs
```bash
php artisan tinker
> ArticleGenerationJob::where('status', 'failed')->latest()->take(10)->get();
```

## Troubleshooting

### Queue Worker Not Running
```bash
# Check if running
ps aux | grep "queue:work"

# Start queue worker
php artisan queue:work --daemon

# Or with Supervisor (recommended for production)
# See: STUCK_JOBS_FIX_DOCUMENTATION.md
```

### Jobs Not Processing
1. Check queue worker is running
2. Check Laravel logs: `storage/logs/laravel.log`
3. Run cleanup: `php artisan articles:fix-stuck-jobs --force`
4. Check database connection

### Notification Not Updating
1. The notification polls every 5 seconds
2. Refresh the page if needed
3. Check browser console for errors
4. Verify API endpoint: `/superadmin/api/generation-jobs`

## Files Modified

- `app/Jobs/GenerateGlobalAIArticleJob.php` - Enhanced error handling
- `app/Models/ArticleGenerationJob.php` - Improved status methods
- `app/Console/Commands/FixStuckGenerationJobs.php` - New cleanup command
- `routes/console.php` - Scheduled cleanup task

## Logs to Monitor

**Application Log:** `storage/logs/laravel.log`

Look for:
- `"Fixed stuck processing job"` - Auto-cleanup occurred
- `"HYBRID MODE CRITICAL ERROR"` - Major failure
- `"Job X marked as failed"` - Job failed gracefully

**Queue Worker Log:** Configure in Supervisor or redirect stdout

---

For full documentation, see: `STUCK_JOBS_FIX_DOCUMENTATION.md`
