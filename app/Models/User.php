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
        'status',
        'openai_api_key',
        'ai_model',
        'ai_default_tone',
        'article_generation_mode',
        'max_variations',
    ];

    /**
     * Get the role that belongs to the user.
     */
    public function roleRelation()
    {
        return $this->belongsTo(Role::class, 'role_id');
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
        ];
    }
}
