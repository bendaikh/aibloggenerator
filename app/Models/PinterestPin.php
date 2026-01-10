<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PinterestPin extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'article_id',
        'user_id',
        'title',
        'description',
        'link',
        'top_image',
        'bottom_image',
        'generated_image',
        'headline_text',
        'subheadline_text',
        'headline_font',
        'subheadline_font',
        'headline_color',
        'subheadline_color',
        'overlay_color',
        'overlay_opacity',
        'status',
        'error_message',
    ];

    protected $casts = [
        'overlay_opacity' => 'integer',
    ];

    protected $appends = ['generated_image_url', 'top_image_url', 'bottom_image_url'];

    /**
     * Get the website that owns the pin.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get the article associated with the pin.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Get the user who created the pin.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full URL for the generated Pinterest image.
     */
    public function getGeneratedImageUrlAttribute(): ?string
    {
        if (!$this->generated_image) {
            return null;
        }

        if (filter_var($this->generated_image, FILTER_VALIDATE_URL)) {
            return $this->generated_image;
        }

        return asset($this->generated_image);
    }

    /**
     * Get the full URL for the top image.
     */
    public function getTopImageUrlAttribute(): ?string
    {
        if (!$this->top_image) {
            return null;
        }

        if (filter_var($this->top_image, FILTER_VALIDATE_URL)) {
            return $this->top_image;
        }

        return asset($this->top_image);
    }

    /**
     * Get the full URL for the bottom image.
     */
    public function getBottomImageUrlAttribute(): ?string
    {
        if (!$this->bottom_image) {
            return null;
        }

        if (filter_var($this->bottom_image, FILTER_VALIDATE_URL)) {
            return $this->bottom_image;
        }

        return asset($this->bottom_image);
    }

    /**
     * Scope for pending pins.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for generated pins.
     */
    public function scopeGenerated($query)
    {
        return $query->where('status', 'generated');
    }

    /**
     * Scope for failed pins.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Mark the pin as generated.
     */
    public function markAsGenerated(string $imagePath): void
    {
        $this->update([
            'status' => 'generated',
            'generated_image' => $imagePath,
            'error_message' => null,
        ]);
    }

    /**
     * Mark the pin as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
