<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'website_id',
        'domain',
        'status',
        'notes',
        'rejection_reason',
        'parked_at',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'parked_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Possible status values.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PARKING = 'parking';
    const STATUS_PARKED = 'parked';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the user who submitted the domain request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the website this domain is assigned to (after approval).
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    /**
     * Get the superadmin who approved the request.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for domains being parked.
     */
    public function scopeParking($query)
    {
        return $query->where('status', self::STATUS_PARKING);
    }

    /**
     * Scope for parked domains awaiting final approval.
     */
    public function scopeParked($query)
    {
        return $query->where('status', self::STATUS_PARKED);
    }

    /**
     * Scope for approved domains.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Check if the domain request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the domain is being parked.
     */
    public function isParking(): bool
    {
        return $this->status === self::STATUS_PARKING;
    }

    /**
     * Check if the domain is parked.
     */
    public function isParked(): bool
    {
        return $this->status === self::STATUS_PARKED;
    }

    /**
     * Check if the domain request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the domain request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Mark domain as being parked.
     */
    public function markAsParking(): bool
    {
        return $this->update([
            'status' => self::STATUS_PARKING,
        ]);
    }

    /**
     * Mark domain as parked.
     */
    public function markAsParked(): bool
    {
        return $this->update([
            'status' => self::STATUS_PARKED,
            'parked_at' => now(),
        ]);
    }

    /**
     * Approve the domain request.
     */
    public function approve(int $approverId, ?int $websiteId = null): bool
    {
        return $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $approverId,
            'website_id' => $websiteId,
        ]);
    }

    /**
     * Reject the domain request.
     */
    public function reject(string $reason = null): bool
    {
        return $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);
    }
}
