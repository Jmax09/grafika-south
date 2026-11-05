<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'price',
        'base_price',
        'is_active',
        'is_featured',
        'requires_design_file',
        'requires_custom_terms',
        'custom_production_time_minutes',
        'enable_production_time',
        'image_path',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'requires_design_file' => 'boolean',
        'requires_custom_terms' => 'boolean',
        'custom_production_time_minutes' => 'integer',
        'enable_production_time' => 'boolean',
    ];

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->price ?: $this->base_price);
    }

    /**
     * Whether the estimated production time feature is enabled for this product.
     * Defaults to enabled (true) when the flag has never been stored (old rows / unsaved models).
     */
    public function productionTimeEnabled(): bool
    {
        return (bool) ($this->enable_production_time ?? true);
    }

    public function getIsCustomizableAttribute(): bool
    {
        return (bool) ($this->requires_design_file || $this->requires_custom_terms
            || ($this->productionTimeEnabled() && $this->custom_production_time_minutes > 0));
    }

    public function getFormattedProductionTimeAttribute(): ?string
    {
        if (!$this->is_customizable) {
            return null;
        }

        $mins = $this->getEstimatedProductionTime(1);
        if ($mins <= 0) {
            return null;
        }
        return self::formatMinutes($mins);
    }

    public static function formatMinutes(?int $minutes): ?string
    {
        if ($minutes === null || $minutes <= 0) {
            return null;
        }
        if ($minutes < 60) {
            return $minutes . ' ' . ($minutes === 1 ? 'min' : 'mins');
        }
        $hours = intdiv($minutes, 60);
        $remMins = $minutes % 60;
        if ($hours < 24) {
            if ($remMins > 0) {
                return "{$hours} hr" . ($hours > 1 ? 's' : '') . " {$remMins} min" . ($remMins > 1 ? 's' : '');
            }
            return "{$hours} hr" . ($hours > 1 ? 's' : '');
        }
        $days = intdiv($hours, 24);
        $remHours = $hours % 24;
        if ($remHours > 0) {
            return "{$days} day" . ($days > 1 ? 's' : '') . " {$remHours} hr" . ($remHours > 1 ? 's' : '');
        }
        return "{$days} day" . ($days > 1 ? 's' : '');
    }

    /**
     * Calculate estimated production time in minutes.
     * Considers product-level minimum completion time, Bill of Materials (BOM) components time,
     * and auto-calculates total required minutes based on ordered quantity.
     * Note: Only applies to custom orders.
     */
    public function getEstimatedProductionTime(int $quantity = 1, ?array $selectedOptions = null): int
    {
        if (!$this->is_customizable || !$this->productionTimeEnabled()) {
            return 0;
        }

        $productMinTime = (int) ($this->custom_production_time_minutes ?? 0);

        // Sum materials production time from product base materials
        $bomTime = 0;
        if ($this->relationLoaded('materials')) {
            foreach ($this->materials as $mat) {
                if ($mat->production_time_minutes > 0) {
                    $bomTime += (int) $mat->production_time_minutes;
                }
            }
        } else {
            foreach ($this->materials()->get() as $mat) {
                if ($mat->production_time_minutes > 0) {
                    $bomTime += (int) $mat->production_time_minutes;
                }
            }
        }

        // Check option-specific BOM materials if loaded or queried
        if (!empty($selectedOptions)) {
            if ($this->relationLoaded('options')) {
                $options = $this->options;
            } else {
                $options = $this->options()->with('materials')->get();
            }

            foreach ($selectedOptions as $optName => $optVal) {
                $cleanVal = explode(' (+₱', (string) $optVal)[0];
                $matchedOption = $options->first(function ($o) use ($optName, $cleanVal, $optVal) {
                    return $o->option_name === $optName && ($o->option_value === $cleanVal || $o->option_value === $optVal);
                });

                if ($matchedOption) {
                    if ($matchedOption->relationLoaded('materials')) {
                        foreach ($matchedOption->materials as $m) {
                            if ($m->production_time_minutes > 0) {
                                $bomTime += (int) $m->production_time_minutes;
                            }
                        }
                    } else {
                        foreach ($matchedOption->materials()->get() as $m) {
                            if ($m->production_time_minutes > 0) {
                                $bomTime += (int) $m->production_time_minutes;
                            }
                        }
                    }
                }
            }
        }

        $baseUnitTime = max($productMinTime, $bomTime);
        if ($baseUnitTime <= 0) {
            $baseUnitTime = 30; // Automatic minimum completion time (30 mins) for customized items
        }

        $qty = max(1, $quantity);
        return $baseUnitTime * $qty;
    }



    /**
     * Get all raw material requirements for this product and its selected options.
     *
     * @param int $quantity
     * @param array|null $selectedOptions
     * @return array
     */
    public function getRequiredMaterials(int $quantity = 1, ?array $selectedOptions = null): array
    {
        $requirements = [];

        // 1. Add product base materials (Main BOM recipe)
        foreach ($this->materials as $material) {
            $requirements[$material->id] = [
                'material' => $material,
                'quantity' => (float) $material->pivot->quantity_used * $quantity,
            ];
        }

        // 2. Apply product option-specific materials (Extra Material Add-on BOM)
        // Options using "Inherited from main BOM" have no custom materials and add nothing to the stack.
        // Option values with explicit add-on materials only add their specific quantities, preventing duplicate deductions.
        if (!empty($selectedOptions)) {
            foreach ($selectedOptions as $optionName => $optionValue) {
                $productOption = ProductOption::resolveFromFormatted($this->id, $optionName, $optionValue);
                if ($productOption && $productOption->materials->isNotEmpty()) {
                    foreach ($productOption->materials as $material) {
                        $qtyNeeded = (float) $material->pivot->quantity_used * $quantity;
                        if (isset($requirements[$material->id])) {
                            // Material already exists in base requirements: add the extra add-on quantity
                            $requirements[$material->id]['quantity'] += $qtyNeeded;
                        } else {
                            // Distinct additional material required for this option
                            $requirements[$material->id] = [
                                'material' => $material,
                                'quantity' => $qtyNeeded,
                            ];
                        }
                    }
                }
            }
        }

        return $requirements;
    }

    /**
     * Check if all associated raw materials in the BOM have sufficient current stock.
     *
     * A product has sufficient stock as long as every required raw material's current_stock
     * is greater than 0 and meets or exceeds the required BOM quantity.
     *
     * A product is OUT OF STOCK ONLY IF raw_material.current_stock < bom_item.quantity_required.
     * Raw materials being in a 'Low Stock' state (i.e. current_stock <= low_stock_threshold)
     * is an administrative alert only and does NOT make the product out of stock for customers.
     *
     * @param int $quantity
     * @param array|null $selectedOptions
     * @return bool
     */
    public function checkBOMStock(int $quantity = 1, ?array $selectedOptions = null): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        $requirements = $this->getRequiredMaterials($quantity, $selectedOptions);

        if (empty($requirements)) {
            return true;
        }

        foreach ($requirements as $req) {
            $material = $req['material'];
            $requiredQuantity = (float) $req['quantity'];

            if ($requiredQuantity <= 0) {
                continue;
            }

            // Sellable stock = physical stock minus what is already reserved by
            // pending orders. Reserved units are spoken for, so counting them
            // would let the storefront oversell and push stock negative on approval.
            $availableStock = max(0, (float) ($material->available_stock ?? 0));

            // A product is OUT OF STOCK ONLY IF available stock < required quantity
            if ($availableStock <= 0 || $availableStock < $requiredQuantity) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if this product is out of stock for a given quantity and options.
     * A product is OUT OF STOCK ONLY IF raw_material.current_stock < bom_item.quantity_required.
     * Low stock threshold is an admin inventory alert only and does not mark products out of stock.
     *
     * @param int $quantity
     * @param array|null $selectedOptions
     * @return bool
     */
    public function isOutOfStock(int $quantity = 1, ?array $selectedOptions = null): bool
    {
        return !$this->checkBOMStock($quantity, $selectedOptions);
    }

    /**
     * Accessor to check if at least 1 unit of this product can be produced from current BOM stocks.
     */
    public function getHasSufficientStockAttribute(): bool
    {
        return $this->checkBOMStock(1);
    }

    /**
     * Synonym accessor for has_sufficient_stock.
     */
    public function getIsInStockAttribute(): bool
    {
        return $this->has_sufficient_stock;
    }

    /**
     * Accessor to check if this product is out of stock.
     */
    public function getIsOutOfStockAttribute(): bool
    {
        return $this->isOutOfStock(1);
    }

    /**
     * Calculate maximum producible quantity based on current raw material inventory levels
     * and optional selected options overrides.
     *
     * @param array|null $selectedOptions
     * @return int
     */
    public function getMaxProducibleQuantity(?array $selectedOptions = null): int
    {
        $requirements = $this->getRequiredMaterials(1, $selectedOptions);
        if (empty($requirements)) {
            return 9999;
        }

        $maxUnits = [];
        foreach ($requirements as $req) {
            $material = $req['material'];
            $qtyNeeded = (float) $req['quantity'];
            if ($qtyNeeded <= 0) {
                continue;
            }
            $availableStock = max(0, (float) ($material->available_stock ?? 0));
            $availableUnits = (int) floor($availableStock / $qtyNeeded);
            $maxUnits[] = max(0, $availableUnits);
        }

        return empty($maxUnits) ? 9999 : min($maxUnits);
    }

    /**
     * Calculate maximum producible quantity based on current raw material inventory levels.
     */
    public function getMaxProducibleQuantityAttribute(): int
    {
        return $this->getMaxProducibleQuantity(null);
    }

    /**
     * Get list of BOM material shortages for a given requested quantity.
     */
    public function getBOMShortages(int $quantity = 1, ?array $selectedOptions = null): array
    {
        $shortages = [];
        $requirements = $this->getRequiredMaterials($quantity, $selectedOptions);

        foreach ($requirements as $req) {
            $material = $req['material'];
            $required = (float) $req['quantity'];
            if ($required <= 0) {
                continue;
            }
            $availableStock = max(0, (float) ($material->available_stock ?? 0));
            if ($availableStock < $required) {
                $shortages[] = [
                    'material' => $material->name,
                    'unit' => $material->unit,
                    'required' => $required,
                    'available' => $availableStock,
                    'deficit' => round($required - $availableStock, 4),
                ];
            }
        }
        return $shortages;
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'product_materials')
                    ->withPivot('id', 'quantity_used')
                    ->withTimestamps();
    }

    public function productMaterials(): HasMany
    {
        return $this->hasMany(ProductMaterial::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true)->orderBy('sort_order');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(ProductVideo::class)->orderBy('sort_order');
    }

    public function primaryVideo(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductVideo::class)->where('is_primary', true)->orderByDesc('is_primary');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }
}
