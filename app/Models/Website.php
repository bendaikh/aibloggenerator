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
        'theme_id',
        'theme_settings',
        'social_media',
        'hbagency_script',
        'ads_txt',
        'pinterest_verification',
        'hbagency_placements',
        'hbagency_active',
        'google_adsense_id',
        'google_ads_placements',
        'google_ads_active',
        'is_active',
        'published_at',
        // SEO Settings
        'seo_settings',
        'google_verification',
        'google_verification_method',
        'google_verification_file',
        'google_verification_file_content',
        'bing_verification',
        'yandex_verification',
        'robots_txt',
        'google_analytics_id',
        'gtm_id',
        // GEO Settings (Generative Engine Optimization)
        'geo_settings',
    ];

    protected $appends = [
        'url',
        'logo_url',
        'favicon_url',
        'google_verification_file_url',
        'is_google_search_console_connected',
        'is_google_analytics_connected',
    ];

    protected $casts = [
        'theme_settings' => 'array',
        'social_media' => 'array',
        'hbagency_placements' => 'array',
        'google_ads_placements' => 'array',
        'seo_settings' => 'array',
        'geo_settings' => 'array',
        'is_active' => 'boolean',
        'hbagency_active' => 'boolean',
        'google_ads_active' => 'boolean',
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
     * Get the theme that belongs to the website.
     */
    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * Theme slug from theme_id (not the legacy `theme` string column).
     */
    public function themeSlug(): ?string
    {
        return $this->theme()->value('slug');
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
     * Get the authors for the website.
     */
    public function authors(): HasMany
    {
        return $this->hasMany(Author::class);
    }

    /**
     * Get the pages for the website.
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * Get the Pinterest pins for the website.
     */
    public function pinterestPins(): HasMany
    {
        return $this->hasMany(PinterestPin::class);
    }

    /**
     * Get the products for the website.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get published products.
     */
    public function publishedProducts(): HasMany
    {
        return $this->products()
            ->where('status', 'published')
            ->orderBy('order')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the orders for the website.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the domain request associated with this website.
     */
    public function domainRequest()
    {
        return $this->hasOne(DomainRequest::class);
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
        // If a custom domain is set, use it
        if ($this->domain) {
            return 'https://' . $this->domain;
        }
        
        // Always use path-based URL (/site/slug) - works on both local and production
        return url('/site/' . $this->slug);
    }

    /**
     * Get a URL for a specific path on this website.
     */
    public function getUrlForPath(string $path): string
    {
        $baseUrl = $this->url;
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Get the full URL for the logo.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        // If it's already a full URL, return it as is
        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }

        // Otherwise, convert relative path to full URL
        return asset($this->logo);
    }

    /**
     * Get the public URL for the Google HTML verification file.
     */
    public function getGoogleVerificationFileUrlAttribute(): ?string
    {
        if ($this->google_verification_method !== 'html_file' || empty($this->google_verification_file)) {
            return null;
        }

        return rtrim($this->url, '/') . '/' . $this->google_verification_file;
    }

    /**
     * Determine if Google Search Console verification is configured.
     */
    public function getIsGoogleSearchConsoleConnectedAttribute(): bool
    {
        if ($this->google_verification_method === 'html_file') {
            return !empty($this->google_verification_file)
                && !empty($this->google_verification_file_content);
        }

        return !empty($this->google_verification);
    }

    /**
     * Determine if Google Analytics is configured.
     */
    public function getIsGoogleAnalyticsConnectedAttribute(): bool
    {
        return !empty($this->google_analytics_id) || !empty($this->gtm_id);
    }

    /**
     * Get the full URL for the favicon.
     */
    public function getFaviconUrlAttribute(): ?string
    {
        if (!$this->favicon) {
            return null;
        }

        // If it's already a full URL, return it as is
        if (filter_var($this->favicon, FILTER_VALIDATE_URL)) {
            return $this->favicon;
        }

        // Otherwise, convert relative path to full URL
        return asset($this->favicon);
    }
}

