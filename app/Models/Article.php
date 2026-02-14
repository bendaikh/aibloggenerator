<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'category_id',
        'user_id',
        'author_id',
        'master_article_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'notes',
        'featured_image',
        'secondary_image',
        'meta_title',
        'meta_description',
        'meta_tags',
        'gradients',
        'status',
        'generation_type',
        'generation_mode',
        'article_type',
        'variation_index',
        'variation_metadata',
        'views',
        'ai_generated',
        'published_at',
        'prep_time',
        'cook_time',
        'rest_time',
        'total_time',
    ];

    protected $appends = ['url', 'processed_content', 'processed_featured_image', 'processed_secondary_image'];

    protected $casts = [
        'meta_tags' => 'array',
        'notes' => 'array',
        'gradients' => 'array',
        'variation_metadata' => 'array',
        'views' => 'integer',
        'ai_generated' => 'boolean',
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
     * Get the author of the article.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * Get the Pinterest pins for the article.
     */
    public function pinterestPins(): HasMany
    {
        return $this->hasMany(PinterestPin::class);
    }

    /**
     * Get the master article if this is a variation.
     */
    public function masterArticle(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'master_article_id');
    }

    /**
     * Get all variations of this article if it's a master.
     */
    public function variations(): HasMany
    {
        return $this->hasMany(Article::class, 'master_article_id');
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
     * Scope a query to only include master articles (not variations).
     */
    public function scopeMasterOnly($query)
    {
        return $query->whereNull('master_article_id');
    }

    /**
     * Scope a query to only include variation articles.
     */
    public function scopeVariationsOnly($query)
    {
        return $query->whereNotNull('master_article_id');
    }

    /**
     * Scope a query to filter by generation mode.
     */
    public function scopeByGenerationMode($query, string $mode)
    {
        return $query->where('generation_mode', $mode);
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
        // Strip trailing ID pattern (e.g., -431) from slug for cleaner URLs
        $cleanSlug = preg_replace('/-\d+$/', '', $this->slug);
        
        // If article_type is 'article', use root path; otherwise use /recipes/ prefix
        if ($this->article_type === 'article') {
            return $this->website->getUrlForPath($cleanSlug);
        }
        
        return $this->website->getUrlForPath('recipes/' . $cleanSlug);
    }

    /**
     * Check if this article is a recipe type.
     */
    public function isRecipe(): bool
    {
        return $this->article_type === 'recipe' || $this->article_type === null;
    }

    /**
     * Check if this article is a regular article type.
     */
    public function isArticle(): bool
    {
        return $this->article_type === 'article';
    }

    /**
     * Check if this article is a master article (not a variation).
     */
    public function isMaster(): bool
    {
        return $this->master_article_id === null;
    }

    /**
     * Check if this article is a variation.
     */
    public function isVariation(): bool
    {
        return $this->master_article_id !== null;
    }

    /**
     * Check if this article was generated using hybrid rewrite mode.
     */
    public function isHybridRewrite(): bool
    {
        return $this->generation_mode === 'hybrid_rewrite';
    }

    /**
     * Get the total number of variations for this article.
     * Returns 0 if this is a variation itself.
     */
    public function getVariationCount(): int
    {
        if ($this->isVariation()) {
            return 0;
        }
        
        return $this->variations()->count();
    }

    /**
     * Get the processed content with fixed image URLs.
     * This ensures images are accessible regardless of domain changes.
     */
    public function getProcessedContentAttribute(): string
    {
        $content = $this->attributes['content'] ?? $this->content ?? '';
        
        if (empty($content)) {
            return '';
        }
        
        // If request() is not available (e.g., in console commands), return original content
        if (!app()->runningInConsole() && !request()) {
            return $content;
        }
        
        // Pattern to match img tags with src attributes
        $pattern = '/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i';
        
        $content = preg_replace_callback($pattern, function ($matches) {
            $imageUrl = $matches[1];
            $originalTag = $matches[0];
            
            // If request() is not available, return original
            if (!request()) {
                return $originalTag;
            }
            
            // Skip if it's already a valid external URL (not localhost/127.0.0.1 and not pointing to uploads)
            if (preg_match('/^https?:\/\//i', $imageUrl)) {
                $parsedUrl = parse_url($imageUrl);
                
                // If it's a valid external URL (not localhost and not our uploads path), keep it
                if (isset($parsedUrl['host']) && 
                    $parsedUrl['host'] !== 'localhost' && 
                    $parsedUrl['host'] !== '127.0.0.1' &&
                    strpos($parsedUrl['path'] ?? '', '/uploads/images/') === false) {
                    return $originalTag; // Already correct external URL
                }
                
                // It's localhost or has uploads path - rebuild with current domain
                if (isset($parsedUrl['path'])) {
                    $path = $parsedUrl['path'];
                    $scheme = request()->getScheme();
                    $host = request()->getHost();
                    $port = request()->getPort();
                    $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
                    $fixedUrl = $scheme . '://' . $host . $portString . $path;
                    return str_replace($imageUrl, $fixedUrl, $originalTag);
                }
            }
            
            // If it's just a filename (UUID pattern), add the full path
            if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\.(png|jpg|jpeg|gif|webp)$/i', $imageUrl)) {
                $scheme = request()->getScheme();
                $host = request()->getHost();
                $port = request()->getPort();
                $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
                $fixedUrl = $scheme . '://' . $host . $portString . '/uploads/images/article/' . $imageUrl;
                return str_replace($imageUrl, $fixedUrl, $originalTag);
            }
            
            // If it's a relative path starting with uploads/images/, make it absolute
            if (strpos($imageUrl, 'uploads/images/') === 0) {
                $scheme = request()->getScheme();
                $host = request()->getHost();
                $port = request()->getPort();
                $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
                $fixedUrl = $scheme . '://' . $host . $portString . '/' . $imageUrl;
                return str_replace($imageUrl, $fixedUrl, $originalTag);
            }
            
            // If it starts with /uploads/images/, make it absolute
            if (strpos($imageUrl, '/uploads/images/') === 0) {
                $scheme = request()->getScheme();
                $host = request()->getHost();
                $port = request()->getPort();
                $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
                $fixedUrl = $scheme . '://' . $host . $portString . $imageUrl;
                return str_replace($imageUrl, $fixedUrl, $originalTag);
            }
            
            return $originalTag; // No change needed
        }, $content);
        
        return $content;
    }

    /**
     * Get the processed featured image URL with fixed domain.
     * This ensures featured images are accessible regardless of domain changes.
     */
    public function getProcessedFeaturedImageAttribute(): ?string
    {
        $featuredImage = $this->attributes['featured_image'] ?? $this->featured_image ?? null;
        
        if (empty($featuredImage)) {
            return null;
        }
        
        // If request() is not available (e.g., in console commands), return original
        if (!request()) {
            return $featuredImage;
        }
        
        // If it's already a full URL (http/https), check if it needs fixing
        if (preg_match('/^https?:\/\//i', $featuredImage)) {
            $parsedUrl = parse_url($featuredImage);
            
            // If it's a valid external URL (not localhost and not our uploads path), keep it
            if (isset($parsedUrl['host']) && 
                $parsedUrl['host'] !== 'localhost' && 
                $parsedUrl['host'] !== '127.0.0.1' &&
                strpos($parsedUrl['path'] ?? '', '/uploads/images/') === false) {
                return $featuredImage; // Already correct external URL
            }
            
            // It's localhost or has uploads path - rebuild with current domain
            if (isset($parsedUrl['path'])) {
                $path = $parsedUrl['path'];
                $scheme = request()->getScheme();
                $host = request()->getHost();
                $port = request()->getPort();
                $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
                return $scheme . '://' . $host . $portString . $path;
            }
        }
        
        // If it's just a filename (UUID pattern), add the full path
        if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\.(png|jpg|jpeg|gif|webp)$/i', $featuredImage)) {
            $scheme = request()->getScheme();
            $host = request()->getHost();
            $port = request()->getPort();
            $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
            return $scheme . '://' . $host . $portString . '/uploads/images/article/' . $featuredImage;
        }
        
        // If it's a relative path starting with uploads/images/, make it absolute
        if (strpos($featuredImage, 'uploads/images/') === 0) {
            $scheme = request()->getScheme();
            $host = request()->getHost();
            $port = request()->getPort();
            $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
            return $scheme . '://' . $host . $portString . '/' . $featuredImage;
        }
        
        // If it starts with /uploads/images/, make it absolute
        if (strpos($featuredImage, '/uploads/images/') === 0) {
            $scheme = request()->getScheme();
            $host = request()->getHost();
            $port = request()->getPort();
            $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
            return $scheme . '://' . $host . $portString . $featuredImage;
        }
        
        return $featuredImage; // Return as-is if no pattern matches
    }

    /**
     * Get the processed secondary image URL with fixed domain.
     */
    public function getProcessedSecondaryImageAttribute(): ?string
    {
        $secondaryImage = $this->attributes['secondary_image'] ?? $this->secondary_image ?? null;
        
        if (empty($secondaryImage)) {
            return null;
        }
        
        // If request() is not available (e.g., in console commands), return original
        if (!request()) {
            return $secondaryImage;
        }
        
        // If it's already a full URL (http/https), check if it needs fixing
        if (preg_match('/^https?:\/\//i', $secondaryImage)) {
            $parsedUrl = parse_url($secondaryImage);
            
            // If it's a valid external URL (not localhost and not our uploads path), keep it
            if (isset($parsedUrl['host']) && 
                $parsedUrl['host'] !== 'localhost' && 
                $parsedUrl['host'] !== '127.0.0.1' &&
                strpos($parsedUrl['path'] ?? '', '/uploads/images/') === false) {
                return $secondaryImage; // Already correct external URL
            }
            
            // It's localhost or has uploads path - rebuild with current domain
            if (isset($parsedUrl['path'])) {
                $path = $parsedUrl['path'];
                $scheme = request()->getScheme();
                $host = request()->getHost();
                $port = request()->getPort();
                $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
                return $scheme . '://' . $host . $portString . $path;
            }
        }
        
        // If it's just a filename (UUID pattern), add the full path
        if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\.(png|jpg|jpeg|gif|webp)$/i', $secondaryImage)) {
            $scheme = request()->getScheme();
            $host = request()->getHost();
            $port = request()->getPort();
            $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
            return $scheme . '://' . $host . $portString . '/uploads/images/article/' . $secondaryImage;
        }
        
        // If it's a relative path starting with uploads/images/, make it absolute
        if (strpos($secondaryImage, 'uploads/images/') === 0) {
            $scheme = request()->getScheme();
            $host = request()->getHost();
            $port = request()->getPort();
            $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
            return $scheme . '://' . $host . $portString . '/' . $secondaryImage;
        }
        
        // If it starts with /uploads/images/, make it absolute
        if (strpos($secondaryImage, '/uploads/images/') === 0) {
            $scheme = request()->getScheme();
            $host = request()->getHost();
            $port = request()->getPort();
            $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
            return $scheme . '://' . $host . $portString . $secondaryImage;
        }
        
        return $secondaryImage; // Return as-is if no pattern matches
    }
}

