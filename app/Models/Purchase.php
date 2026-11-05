<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'supplier_id',
        'reference_no',
        'total_amount',
        'status',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    /**
     * Amount already paid against this specific purchase order.
     */
    public function getPaidAmountAttribute(): float
    {
        if (array_key_exists('total_paid', $this->attributes)) {
            return (float) ($this->attributes['total_paid'] ?? 0);
        }

        return (float) $this->payments()->sum('amount_paid');
    }

    /**
     * Remaining balance for this purchase order.
     */
    public function getBalanceAttribute(): float
    {
        return round((float) $this->total_amount - $this->paid_amount, 2);
    }

    public function getIsReceivedAttribute(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('status', $status) : $query;
    }

    /**
     * Human readable reference prefix, e.g. PO-20261027-0001.
     */
    public static function generateReferenceNo(): string
    {
        $date = now()->format('Ymd');
        $prefix = "PO-{$date}-";

        $lastToday = static::where('reference_no', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('reference_no');

        $sequence = 1;
        if ($lastToday) {
            $sequence = (int) substr($lastToday, strlen($prefix)) + 1;
        }

        $reference = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);

        // Defensive: guarantee uniqueness even under concurrent creation.
        while (static::where('reference_no', $reference)->exists()) {
            $sequence++;
            $reference = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        }

        return $reference;
    }
}
