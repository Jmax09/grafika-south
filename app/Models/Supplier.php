<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
    ];

    public function materials(): HasMany
    {
        return $this->hasMany(SupplierMaterial::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    /**
     * Total value of all non-cancelled purchase orders for this supplier.
     */
    public function getTotalPurchasesAttribute(): float
    {
        if (array_key_exists('total_purchases', $this->attributes)) {
            return (float) ($this->attributes['total_purchases'] ?? 0);
        }

        return (float) $this->purchases()
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
    }

    /**
     * Total amount actually paid to this supplier.
     */
    public function getTotalPaidAttribute(): float
    {
        if (array_key_exists('total_paid', $this->attributes)) {
            return (float) ($this->attributes['total_paid'] ?? 0);
        }

        return (float) $this->payments()->sum('amount_paid');
    }

    /**
     * Accounts payable: purchases still owed to this supplier.
     * A negative value means the supplier has been overpaid (credit).
     */
    public function getBalanceAttribute(): float
    {
        return round($this->total_purchases - $this->total_paid, 2);
    }
}
