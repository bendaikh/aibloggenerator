<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'user_id',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'sale_price',
        'currency',
        'featured_image',
        'gallery_images',
        'sku',
        'stock_quantity',
        'track_stock',
        'is_digital',
        'digital_file_url',
        'external_url',
        'status',
        'is_featured',
        'order',
        'meta_data',
    ];

    protected $appends = ['url', 'processed_featured_image', 'display_price', 'is_on_sale'];

    protected $casts = [
        'gallery_images' => 'array',
        'meta_data' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'track_stock' => 'boolean',
        'is_digital' => 'boolean',
        'is_featured' => 'boolean',
        'order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    /**
     * Get the website that owns the product.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get the user that created the product.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include published products.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include in-stock products.
     */
    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_stock', false)
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    /**
     * Get the public URL for the product.
     */
    public function getUrlAttribute(): string
    {
        return $this->website->getUrlForPath('shop/' . $this->slug);
    }

    /**
     * Check if the product is on sale.
     */
    public function getIsOnSaleAttribute(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    /**
     * Get the display price (sale price if available, otherwise regular price).
     */
    public function getDisplayPriceAttribute(): string
    {
        $price = $this->is_on_sale ? $this->sale_price : $this->price;
        return $this->formatPrice($price);
    }

    /**
     * Format price with currency symbol.
     */
    public function formatPrice($amount): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CAD' => 'C$',
            'AUD' => 'A$',
        ];

        $symbol = $symbols[$this->currency] ?? $this->currency . ' ';
        return $symbol . number_format($amount, 2);
    }

    /**
     * Get the processed featured image URL with fixed domain.
     */
    public function getProcessedFeaturedImageAttribute(): ?string
    {
        $featuredImage = $this->attributes['featured_image'] ?? $this->featured_image ?? null;

        if (empty($featuredImage)) {
            return null;
        }

        if (!request()) {
            return $featuredImage;
        }

        if (preg_match('/^https?:\/\//i', $featuredImage)) {
            $parsedUrl = parse_url($featuredImage);

            if (isset($parsedUrl['host']) &&
                $parsedUrl['host'] !== 'localhost' &&
                $parsedUrl['host'] !== '127.0.0.1' &&
                strpos($parsedUrl['path'] ?? '', '/uploads/images/') === false) {
                return $featuredImage;
            }

            if (isset($parsedUrl['path'])) {
                $path = $parsedUrl['path'];
                $scheme = request()->getScheme();
                $host = request()->getHost();
                $port = request()->getPort();
                $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
                return $scheme . '://' . $host . $portString . $path;
            }
        }

        if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\.(png|jpg|jpeg|gif|webp)$/i', $featuredImage)) {
            $scheme = request()->getScheme();
            $host = request()->getHost();
            $port = request()->getPort();
            $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
            return $scheme . '://' . $host . $portString . '/uploads/images/product/' . $featuredImage;
        }

        if (strpos($featuredImage, 'uploads/images/') === 0) {
            $scheme = request()->getScheme();
            $host = request()->getHost();
            $port = request()->getPort();
            $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
            return $scheme . '://' . $host . $portString . '/' . $featuredImage;
        }

        if (strpos($featuredImage, '/uploads/images/') === 0) {
            $scheme = request()->getScheme();
            $host = request()->getHost();
            $port = request()->getPort();
            $portString = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
            return $scheme . '://' . $host . $portString . $featuredImage;
        }

        return $featuredImage;
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        if (!$this->track_stock) {
            return true;
        }

        return $this->stock_quantity > 0;
    }
}
