<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'meta_title',
        'meta_description',
        'order',
        'is_active',
        'show_in_menu',
        'is_default',
    ];

    protected $appends = ['url'];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_menu' => 'boolean',
        'is_default' => 'boolean',
        'order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    /**
     * Get the website that owns the page.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get the public URL for the page.
     */
    public function getUrlAttribute(): string
    {
        // If a custom domain is set, use it
        if ($this->website->domain) {
            return 'https://' . $this->website->domain . '/page/' . $this->slug;
        }
        
        // Always use path-based URL (/site/slug/page/page-slug) - works on both local and production
        return url('/site/' . $this->website->slug . '/page/' . $this->slug);
    }
}

