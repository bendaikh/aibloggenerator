<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Website extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'subdomain',
        'domain',
        'description',
        'logo',
        'favicon',
        'theme',
        'theme_settings',
        'social_media',
        'is_active',
        'published_at',
    ];

    protected $appends = ['url'];

    protected $casts = [
        'theme_settings' => 'array',
        'social_media' => 'array',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($website) {
            if (empty($website->slug)) {
                $website->slug = Str::slug($website->name);
            }
            
            // Auto-generate subdomain from slug if not provided
            if (empty($website->subdomain)) {
                $website->subdomain = $website->slug;
            }
        });
    }

    /**
     * Get the user that owns the website.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the articles for the website.
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Get the categories for the website.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get published articles.
     */
    public function publishedArticles(): HasMany
    {
        return $this->articles()
            ->where('status', 'published')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc');
    }

    /**
     * Get the public URL for the website.
     */
    public function getUrlAttribute(): string
    {
        if ($this->domain) {
            return 'https://' . $this->domain;
        }
        
        // If in local environment or if base_domain is localhost, use path-based URL
        if (config('app.env') === 'local' || config('app.base_domain') === 'localhost') {
            return url('/site/' . $this->slug);
        }
        
        // Return subdomain URL for production
        $baseDomain = config('app.base_domain', 'localhost');
        $scheme = config('app.env') === 'production' ? 'https://' : 'http://';
        
        // If we're in a request, use the current scheme
        if (request()) {
            $scheme = request()->isSecure() ? 'https://' : 'http://';
        }
        
        return $scheme . $this->subdomain . '.' . $baseDomain;
    }

    /**
     * Get a URL for a specific path on this website.
     */
    public function getUrlForPath(string $path): string
    {
        $baseUrl = $this->url;
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

