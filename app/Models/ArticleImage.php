<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleImage extends Model
{
    use HasFactory;

    protected $appends = [
        'url',
    ];

    protected $fillable = [
        'article_id',
        'user_id',
        'title',
        'prompt',
        'revised_prompt',
        'original_url',
        'local_path',
        'position',
        'size',
        'quality',
        'style',
        'cost',
        'generation_type',
        'metadata',
    ];

    protected $casts = [
        'cost' => 'decimal:6',
        'metadata' => 'array',
        'position' => 'integer',
    ];

    /**
     * Get the article that owns this image
     */
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Get the user who generated this image
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the processed image URL (handles local paths, storage, and public uploads)
     */
    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->local_path, 'http')) {
            return $this->local_path;
        }

        // Already a valid public path (storage or uploads)
        if (str_starts_with($this->local_path, '/storage/') || str_starts_with($this->local_path, '/uploads/')) {
            return $this->local_path;
        }

        // Legacy: Assume storage path if no prefix
        return '/storage/' . ltrim($this->local_path, '/');
    }
}
