<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    /**
     * Compact display labels for the units the inventory form accepts, so a supplier
     * package can read "1 Gallon = 3.785 L" instead of echoing the raw spelling.
     */
    public const UNIT_LABELS = [
        'ml' => 'ml',
        'milliliter' => 'ml',
        'milliliters' => 'ml',
        'millilitre' => 'ml',
        'millilitres' => 'ml',
        'l' => 'L',
        'liter' => 'L',
        'liters' => 'L',
        'litre' => 'L',
        'litres' => 'L',
    ];

    protected $fillable = [
        'name',
        'category',
        'unit',
        'current_stock',
        'reserved_stock',
        'low_stock_threshold',
        'unit_cost',
        'production_time_minutes',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'reserved_stock' => 'decimal:2',
        'low_stock_threshold' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'production_time_minutes' => 'integer',
    ];

    public function getFormattedProductionTimeAttribute(): ?string
    {
        if ($this->production_time_minutes === null || $this->production_time_minutes <= 0) {
            return null;
        }
        return Product::formatMinutes($this->production_time_minutes);
    }


    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_materials')
                    ->withPivot('id', 'quantity_used')
                    ->withTimestamps();
    }

    public function productMaterials(): HasMany
    {
        return $this->hasMany(ProductMaterial::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MaterialLog::class);
    }

    /**
     * Effective available stock = physical stock minus reserved.
     */
    public function getAvailableStockAttribute(): float
    {
        return max(0, (float) $this->current_stock - (float) $this->reserved_stock);
    }

    /**
     * Compact base-unit label, e.g. "liters" → "L", "ml" → "ml", "pcs" → "pcs".
     */
    public function getUnitLabelAttribute(): string
    {
        $unit = strtolower(trim((string) $this->unit));

        return self::UNIT_LABELS[$unit] ?? (string) $this->unit;
    }

    /**
     * Low stock is judged on SELLABLE stock (physical minus reserved).
     * Units reserved by pending orders can no longer be promised to anyone else,
     * so they must not count as available stock.
     */
    public function isLowStock(): bool
    {
        return $this->available_stock <= $this->low_stock_threshold;
    }

    /**
     * Nothing sellable left: either the shelf is empty or every unit is reserved.
     */
    public function isOutOfStock(): bool
    {
        return $this->available_stock <= 0;
    }

    /**
     * Calculate estimated prints remaining for a specific ml consumption per print
     */
    public function estimatedPrintsRemaining(float $avgMlPerPrint): int
    {
        if ($avgMlPerPrint <= 0) {
            return 0;
        }

        return (int) floor($this->available_stock / $avgMlPerPrint);
    }
}