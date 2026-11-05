<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'account_name',
        'account_number',
        'qr_code_path',
        'instructions',
        'api_keys',
        'is_online_payment',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_online_payment' => 'boolean',
    ];

    /**
     * Determine if this payment method is Cash on Delivery, Cash on Pickup, or offline cash payment.
     */
    public function isCashOrOfflineMethod(): bool
    {
        if ($this->is_online_payment !== null) {
            return !(bool) $this->is_online_payment;
        }

        $name = strtolower($this->name);
        return str_contains($name, 'cash on delivery')
            || str_contains($name, 'cash on pickup')
            || str_contains($name, 'cash on pick-up')
            || str_contains($name, 'cash on store')
            || str_contains($name, 'pay at print shop')
            || str_contains($name, 'pay upon store collection')
            || str_contains($name, 'cod')
            || str_contains($name, 'cop')
            || (trim($name) === 'cash');
    }

    /**
     * Determine if this payment method is an online digital payment method (GCash, Maya, Bank Transfer, Card, etc.).
     */
    public function isDigitalMethod(): bool
    {
        if ($this->is_online_payment !== null) {
            return (bool) $this->is_online_payment;
        }

        return !$this->isCashOrOfflineMethod();
    }

    /**
     * Accessor for human-readable payment channel name.
     */
    public function getChannelNameAttribute(): string
    {
        return $this->isDigitalMethod() ? 'Online / Digital' : 'Cash / Physical';
    }

    /**
     * Scope query to only active payment methods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to only online digital payment methods.
     */
    public function scopeOnline($query)
    {
        return $query->where('is_online_payment', true);
    }

    /**
     * Scope query to only cash/physical payment methods.
     */
    public function scopeOffline($query)
    {
        return $query->where('is_online_payment', false);
    }

    /**
     * Accessor for full QR Code image asset URL if uploaded.
     */
    public function getQrCodeUrlAttribute()
    {
        if ($this->qr_code_path) {
            return asset('storage/' . $this->qr_code_path);
        }
        return null;
    }
}
