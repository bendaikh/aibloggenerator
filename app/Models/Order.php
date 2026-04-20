<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'website_id',
        'product_id',
        'order_number',
        'customer_email',
        'customer_name',
        'customer_phone',
        'payment_provider',
        'payment_id',
        'payment_intent_id',
        'payment_status',
        'amount',
        'currency',
        'product_snapshot',
        'status',
        'paid_at',
        'fulfilled_at',
        'billing_address',
        'meta_data',
        'notes',
    ];

    protected $casts = [
        'product_snapshot' => 'array',
        'billing_address' => 'array',
        'meta_data' => 'array',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(Str::random(8));
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'fulfilled']);
    }

    public function isFulfilled(): bool
    {
        return $this->status === 'fulfilled';
    }

    public function markAsPaid(string $paymentId = null, string $paymentIntentId = null): void
    {
        $this->update([
            'status' => 'paid',
            'payment_status' => 'completed',
            'payment_id' => $paymentId ?? $this->payment_id,
            'payment_intent_id' => $paymentIntentId ?? $this->payment_intent_id,
            'paid_at' => now(),
        ]);
    }

    public function markAsFulfilled(): void
    {
        $this->update([
            'status' => 'fulfilled',
            'fulfilled_at' => now(),
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'cancelled',
            'payment_status' => 'failed',
        ]);
    }

    public function getFormattedAmountAttribute(): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CAD' => 'C$',
            'AUD' => 'A$',
        ];

        $symbol = $symbols[$this->currency] ?? $this->currency . ' ';
        return $symbol . number_format($this->amount, 2);
    }
}
