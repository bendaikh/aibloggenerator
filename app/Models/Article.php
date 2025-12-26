<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'category_id',
        'user_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'meta_tags',
        'status',
        'generation_type',
        'views',
        'published_at',
    ];

    protected $casts = [
        'meta_tags' => 'array',
        'views' => 'integer',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    /**
     * Get the website that owns the article.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get the category that owns the article.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user that created the article.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include published articles.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include AI-generated articles.
     */
    public function scopeAiGenerated($query)
    {
        return $query->where('generation_type', 'ai');
    }

    /**
     * Scope a query to only include manually created articles.
     */
    public function scopeManual($query)
    {
        return $query->where('generation_type', 'manual');
    }

    /**
     * Increment the views counter.
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /**
     * Get the public URL for the article.
     */
    public function getUrlAttribute(): string
    {
        return route('article.show', [
            'website' => $this->website->slug,
            'article' => $this->slug
        ]);
    }
}

