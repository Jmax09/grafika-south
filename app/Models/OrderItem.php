<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'production_time_minutes',
        'options_selected',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'production_time_minutes' => 'integer',
        'options_selected' => 'array',
    ];

    public function getFormattedProductionTimeAttribute(): ?string
    {
        if ($this->production_time_minutes === null || $this->production_time_minutes <= 0) {
            return null;
        }
        return Product::formatMinutes($this->production_time_minutes);
    }


    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(OrderFile::class);
    }

    public function latestFile()
    {
        return $this->hasOne(OrderFile::class)->latestOfMany();
    }
}
