# Duplicate Job Processing Fixes

## Problem
Articles were showing as "Completed" but then getting reprocessed again, causing confusion and wasted API calls.

## Root Causes Identified

1. **No Job Uniqueness**: The same job could be dispatched multiple times (double-clicks, network retries)
2. **No Status Validation**: Jobs were being marked as "processing" even if already "completed"
3. **Race Conditions**: Multiple queue workers could process the same job simultaneously
4. **No Early Exit**: Jobs didn't check if work was already done before starting

## Fixes Applied

### 1. Job Uniqueness (`GenerateGlobalAIArticleJob.php`)
```php
// Added ShouldBeUnique interface
class GenerateGlobalAIArticleJob implements ShouldQueue, ShouldBeUnique
{
    public $uniqueFor = 3600; // Job is unique for 1 hour
    
    public function uniqueId(): string
    {
        $websiteIdsString = implode(',', $this->websiteIds);
        return "generate-article-{$this->userId}-{$this->topic}-{$websiteIdsString}";
    }
}
```
**Effect**: Laravel will automatically prevent the same job (same user, topic, and websites) from being queued multiple times within 1 hour.

### 2. Early Exit Check (`GenerateGlobalAIArticleJob.php`)
```php
// At the start of handle() method
$allCompleted = true;
foreach ($this->generationJobIds as $jobId) {
    $job = ArticleGenerationJob::find($jobId);
    if ($job && $job->status !== 'completed') {
        $allCompleted = false;
        break;
    }
}

if ($allCompleted) {
    Log::warning('All jobs already completed, aborting');
    return; // Exit early
}
```
**Effect**: If all jobs are already completed, the job exits immediately without doing any work.

### 3. Status Validation (`GenerateGlobalAIArticleJob.php`)
```php
// Only mark as processing if status is 'pending'
foreach ($this->generationJobIds as $jobId) {
    $job = ArticleGenerationJob::find($jobId);
    if ($job && $job->status === 'pending') {
        $job->markAsProcessing();
    } elseif ($job && $job->status === 'completed') {
        Log::warning("Job {$jobId} is already completed, skipping");
    }
}
```
**Effect**: Completed jobs will never be changed back to "processing" status.

### 4. Database Row Locking (`ArticleGenerationJob.php`)
```php
public function markAsProcessing(): void
{
    $freshJob = static::lockForUpdate()->find($this->id);
    
    if (!$freshJob) {
        return; // Job was deleted
    }
    
    // Only update if pending
    if ($freshJob->status === 'pending') {
        $freshJob->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }
}

public function markAsCompleted(?int $articleId = null): void
{
    $freshJob = static::lockForUpdate()->find($this->id);
    
    if (!$freshJob) {
        return;
    }
    
    // Only update if not already completed
    if ($freshJob->status !== 'completed') {
        $freshJob->update([
            'status' => 'completed',
            'article_id' => $articleId,
            'completed_at' => now(),
        ]);
    }
}
```
**Effect**: 
- Uses database row locking to prevent race conditions
- Always fetches fresh data from database before updating
- Only updates status if it makes sense (pending → processing, not-completed → completed)

### 5. Controller-Level Duplicate Prevention (`OrganizationController.php`)
```php
// Check for duplicate pending/processing jobs
$recentJob = ArticleGenerationJob::where('user_id', $user->id)
    ->where('topic', $validated['topic'])
    ->whereIn('status', ['pending', 'processing'])
    ->where('created_at', '>=', now()->subMinutes(2))
    ->first();

if ($recentJob) {
    return back()->withErrors([
        'error' => 'A job for this topic is already being processed.'
    ]);
}
```
**Effect**: Users can't submit the same topic twice within 2 minutes.

### 6. Duplicate Article Prevention (`GenerateGlobalAIArticleJob.php`)
```php
// Check for existing articles before creating new ones
$existingArticle = Article::where('website_id', $websiteId)
    ->where('title', 'LIKE', '%' . substr($this->topic, 0, 30) . '%')
    ->where('generation_mode', 'hybrid_rewrite')
    ->where('created_at', '>=', now()->subMinutes(5))
    ->first();

if ($existingArticle) {
    Log::info("Skipping - duplicate article detected");
    if ($generationJob) {
        $generationJob->markAsCompleted($existingArticle->id);
    }
    continue;
}
```
**Effect**: Even if the job runs twice, it won't create duplicate articles.

## How It Works Now

### Scenario 1: User Submits Form Twice
1. **First submission**: Creates pending jobs, dispatches queue job
2. **Second submission** (within 2 minutes): Shows error "Job already being processed"
3. **Result**: Only one set of jobs created ✅

### Scenario 2: Queue Worker Picks Up Same Job Twice
1. **First pickup**: Job starts, marks jobs as "processing"
2. **Second pickup**: Laravel detects uniqueId and skips (job is unique for 1 hour)
3. **Result**: Job only processes once ✅

### Scenario 3: Job is Retried After Completion
1. **Retry attempt**: Job checks if all jobs are completed
2. **Early exit**: Returns immediately without doing work
3. **Result**: No duplicate processing ✅

### Scenario 4: Race Condition (Multiple Workers)
1. **Worker A**: Locks row, changes status to "processing"
2. **Worker B**: Tries to lock same row, waits
3. **Worker B gets lock**: Sees status is "processing", skips update
4. **Result**: No status conflicts ✅

## Testing

After deploying these fixes:

1. **Test duplicate submission**:
   - Click "Generate" button twice quickly
   - Should see error message on second click

2. **Test job completion**:
   - Generate articles normally
   - Jobs should complete and stay "completed"
   - Should not see "processing" status after "completed"

3. **Test queue worker**:
   - Run `php artisan queue:work`
   - Generate articles
   - Watch logs for "already completed" warnings

## Commands to Clear Queue (if needed)

```bash
# Clear failed jobs
php artisan queue:flush

# Clear cache (releases job uniqueness locks)
php artisan cache:clear

# Restart queue worker
php artisan queue:restart
```

## Monitoring

Check logs for these messages:
- ✅ "All jobs already completed, aborting"
- ✅ "Job {id} is already completed, skipping"
- ✅ "Skipping - duplicate article detected"
- ❌ "Job {id} marked as processing" (after completed)

## Configuration Requirements

Ensure `.env` has:
```
QUEUE_CONNECTION=database
CACHE_STORE=database  # or redis for better performance
```

The `ShouldBeUnique` interface requires a cache driver to work properly.
