<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A supplier's price list entry: one of the shop's inventory materials sold in a
 * supplier-defined package (e.g. 1 Box = 5 L) at a cost per package.
 */
class SupplierMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'inventory_material_id',
        'supplier_unit_name',
        'conversion_qty',
        'unit_cost',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'conversion_qty' => 'decimal:3',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * The shop's inventory material this catalog entry restocks (mandatory).
     */
    public function inventoryMaterial(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'inventory_material_id');
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Display name is always inherited from inventory — catalog rows never type it.
     */
    public function getItemNameAttribute(): string
    {
        return $this->inventoryMaterial?->name ?? 'Removed inventory item';
    }

    /**
     * The supplier's package name, e.g. "Box", "Gallon", "Bottle".
     */
    public function getUnitLabelAttribute(): string
    {
        return (string) $this->supplier_unit_name;
    }

    /**
     * Base unit this package converts into, e.g. "L" or "ml".
     */
    public function getBaseUnitAttribute(): string
    {
        return $this->inventoryMaterial?->unit_label ?? '';
    }

    /**
     * Human readable conversion, e.g. "1 Box = 5 L".
     */
    public function getPackageLabelAttribute(): string
    {
        return sprintf(
            '1 %s = %s %s',
            $this->supplier_unit_name,
            rtrim(rtrim(number_format((float) $this->conversion_qty, 3, '.', ''), '0'), '.'),
            $this->base_unit
        );
    }

    /**
     * Convert a quantity bought in supplier packages into base inventory units.
     */
    public function toBaseUnits(int|float $packages): float
    {
        return round((float) $packages * (float) $this->conversion_qty, 3);
    }

    /**
     * Cost per single base inventory unit, e.g. ₱250 per 5 L box → ₱50 per L.
     */
    public function getBaseUnitCostAttribute(): float
    {
        $conversion = (float) $this->conversion_qty;

        return $conversion > 0 ? round((float) $this->unit_cost / $conversion, 4) : 0.0;
    }
}
