<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleGenerationJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'website_id',
        'article_id',
        'topic',
        'status',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the job.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the website that the job belongs to.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get the article that was created.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Scope a query to only include pending or processing jobs.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'processing']);
    }

    /**
     * Scope a query to only include recent jobs (last 24 hours).
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subHours(24));
    }

    /**
     * Mark job as processing.
     */
    public function markAsProcessing(): void
    {
        // Use fresh() to get the latest state from database
        // and lock the row to prevent race conditions
        $freshJob = static::lockForUpdate()->find($this->id);
        
        if (!$freshJob) {
            return; // Job was deleted
        }
        
        // Only update if not already completed or processing
        if ($freshJob->status === 'pending') {
            $freshJob->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);
        }
    }

    /**
     * Mark job as completed.
     */
    public function markAsCompleted(?int $articleId = null): void
    {
        // Use fresh() to get the latest state from database
        // and lock the row to prevent race conditions
        $freshJob = static::lockForUpdate()->find($this->id);
        
        if (!$freshJob) {
            return; // Job was deleted
        }
        
        // Only update if not already completed (prevents re-marking)
        if ($freshJob->status !== 'completed') {
            $freshJob->update([
                'status' => 'completed',
                'article_id' => $articleId,
                'completed_at' => now(),
            ]);
        }
    }

    /**
     * Mark job as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        // Use fresh() to get the latest state from database
        // and lock the row to prevent race conditions
        $freshJob = static::lockForUpdate()->find($this->id);
        
        if (!$freshJob) {
            return; // Job was deleted
        }
        
        // Only update if not already completed (completed takes precedence over failed)
        if ($freshJob->status !== 'completed') {
            $freshJob->update([
                'status' => 'failed',
                'error_message' => $errorMessage,
                'completed_at' => now(),
            ]);
        }
    }
}

