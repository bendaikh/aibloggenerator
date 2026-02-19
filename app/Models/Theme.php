<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'show_recipe_sections',
        'is_active',
    ];

    protected $casts = [
        'show_recipe_sections' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users using this theme.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the websites using this theme.
     */
    public function websites(): HasMany
    {
        return $this->hasMany(\App\Models\Website::class);
    }
}
