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
     * Get the processed image URL (handles local paths and storage)
     */
    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->local_path, 'http')) {
            return $this->local_path;
        }

        if (str_starts_with($this->local_path, '/storage/')) {
            return $this->local_path;
        }

        return '/storage/' . ltrim($this->local_path, '/');
    }
}
