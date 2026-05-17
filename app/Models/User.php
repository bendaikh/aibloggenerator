<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'status',
        'is_global_user',
        'theme_id',
        'openai_api_key',
        'gemini_api_key',
        'ideogram_api_key',
        'image_generation_provider',
        'ai_model',
        'ai_default_tone',
        'article_generation_mode',
        'max_variations',
        'stripe_publishable_key',
        'stripe_secret_key',
        'stripe_webhook_secret',
        'paypal_client_id',
        'paypal_client_secret',
        'paypal_mode',
        'payments_enabled',
        'twilio_sid',
        'twilio_auth_token',
        'twilio_whatsapp_from',
        'admin_whatsapp_number',
        'security_alerts_enabled',
    ];

    /**
     * Get the role that belongs to the user.
     */
    public function roleRelation()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the theme that belongs to the user.
     */
    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * Check if user is a superadmin.
     */
    public function isSuperAdmin(): bool
    {
        // Check both old string-based role and new role relationship
        return $this->role === 'superadmin' || 
               ($this->roleRelation && $this->roleRelation->name === 'superadmin');
    }

    /**
     * Check if user is a website owner (has full access to their own websites only).
     */
    public function isWebsiteOwner(): bool
    {
        return $this->roleRelation && $this->roleRelation->name === 'website_owner';
    }

    /**
     * Check if user can see all websites (true superadmin only).
     */
    public function canSeeAllWebsites(): bool
    {
        // Nobody should see all websites - each user only sees their own
        // This was changed per user request - all users are isolated to their own websites
        return false;
    }

    /**
     * Check if user is an admin or superadmin.
     */
    public function isAdmin(): bool
    {
        // Check both old string-based role and new role relationship
        $hasOldRole = in_array($this->role, ['admin', 'superadmin']);
        $hasNewRole = $this->roleRelation && 
                      in_array($this->roleRelation->name, ['admin', 'superadmin']);
        
        return $hasOldRole || $hasNewRole;
    }

    /**
     * Check if user account is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if user account is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if user account is declined.
     */
    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permissionName): bool
    {
        if (!$this->roleRelation) {
            return false;
        }

        return $this->roleRelation->hasPermission($permissionName);
    }

    /**
     * Get all permissions for the user through their role.
     */
    public function getPermissions()
    {
        if (!$this->roleRelation) {
            return collect([]);
        }

        return $this->roleRelation->permissions;
    }

    /**
     * Get the websites owned by the user.
     */
    public function websites()
    {
        return $this->hasMany(Website::class);
    }

    /**
     * Secondary users assigned to this global user.
     */
    public function secondaryUsers()
    {
        return $this->belongsToMany(
            User::class,
            'global_user_secondary_users',
            'global_user_id',
            'secondary_user_id'
        )->withTimestamps();
    }

    /**
     * Global users that have this user assigned as secondary.
     */
    public function assignedGlobalUsers()
    {
        return $this->belongsToMany(
            User::class,
            'global_user_secondary_users',
            'secondary_user_id',
            'global_user_id'
        )->withTimestamps();
    }

    public function isGlobalUser(): bool
    {
        return (bool) $this->is_global_user;
    }

    /**
     * Websites this user may access (own, secondary assignments, or all for legacy superadmin flag).
     */
    public function accessibleWebsitesQuery()
    {
        if ($this->isGlobalUser()) {
            $secondaryUserIds = $this->secondaryUsers()->pluck('users.id')->toArray();
            $secondaryUserIds[] = $this->id;

            return Website::query()->whereIn('user_id', $secondaryUserIds);
        }

        if ($this->canSeeAllWebsites()) {
            return Website::query();
        }

        return Website::query()->where('user_id', $this->id);
    }

    public function canAccessWebsite(Website $website): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->id === $website->user_id) {
            return true;
        }

        if ($this->isGlobalUser()) {
            return $this->secondaryUsers()->where('users.id', $website->user_id)->exists();
        }

        return false;
    }

    /**
     * Get the articles created by the user.
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Get the domain requests submitted by the user.
     */
    public function domainRequests()
    {
        return $this->hasMany(DomainRequest::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'openai_api_key',
        'gemini_api_key',
        'ideogram_api_key',
        'stripe_publishable_key',
        'stripe_secret_key',
        'stripe_webhook_secret',
        'paypal_client_id',
        'paypal_client_secret',
        'twilio_sid',
        'twilio_auth_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'openai_api_key' => 'encrypted',
            'gemini_api_key' => 'encrypted',
            'ideogram_api_key' => 'encrypted',
            'stripe_publishable_key' => 'encrypted',
            'stripe_secret_key' => 'encrypted',
            'stripe_webhook_secret' => 'encrypted',
            'paypal_client_id' => 'encrypted',
            'paypal_client_secret' => 'encrypted',
            'twilio_sid' => 'encrypted',
            'twilio_auth_token' => 'encrypted',
            'payments_enabled' => 'boolean',
            'security_alerts_enabled' => 'boolean',
            'is_global_user' => 'boolean',
        ];
    }

    public function hasStripeConfigured(): bool
    {
        return !empty($this->stripe_publishable_key) && !empty($this->stripe_secret_key);
    }

    public function hasPaypalConfigured(): bool
    {
        return !empty($this->paypal_client_id) && !empty($this->paypal_client_secret);
    }

    public function hasPaymentsConfigured(): bool
    {
        return $this->payments_enabled && ($this->hasStripeConfigured() || $this->hasPaypalConfigured());
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Website::class);
    }
}
