<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'option_name',
        'option_value',
        'price_modifier',
        'image_path',
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function materials()
    {
        return $this->belongsToMany(Material::class, 'product_option_materials')
                    ->withPivot('id', 'quantity_used')
                    ->withTimestamps();
    }

    public function productOptionMaterials()
    {
        return $this->hasMany(ProductOptionMaterial::class, 'product_option_id');
    }

    /**
     * Public URL of the optional option image, or null when none is attached.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    /**
     * Lookup key for an option image. Cart/checkout store the value with the
     * price suffix appended ("Large (+₱20.00)"), so the suffix is stripped here.
     */
    public static function imageMapKey(string $optionName, string $optionValue): string
    {
        $cleanValue = trim(preg_replace('/\s*\(\+₱.*\)$/u', '', trim($optionValue)));

        return strtolower(trim($optionName)) . '|' . strtolower($cleanValue);
    }

    /**
     * Map of "name|value" => image URL for every option of a product that has an
     * image. Cached per product so option chip loops stay free of extra queries.
     *
     * @return array<string, string>
     */
    public static function imageMapFor(int $productId): array
    {
        static $cache = [];

        if (isset($cache[$productId])) {
            return $cache[$productId];
        }

        $map = [];
        foreach (self::where('product_id', $productId)->whereNotNull('image_path')->get() as $option) {
            $map[self::imageMapKey($option->option_name, $option->option_value)] = $option->image_url;
        }

        return $cache[$productId] = $map;
    }

    public static function resolveFromFormatted(int $productId, string $optionName, string $formattedValue): ?ProductOption
    {
        $cleanOptionName = trim($optionName);
        $cleanValue = trim(preg_replace('/\s*\(\+₱.*\)$/u', '', $formattedValue));

        $options = self::where('product_id', $productId)
            ->where(function ($q) use ($cleanOptionName) {
                $q->where('option_name', $cleanOptionName)
                  ->orWhereRaw('LOWER(option_name) = ?', [strtolower($cleanOptionName)]);
            })
            ->get();

        foreach ($options as $option) {
            $modifier = (float) $option->price_modifier;
            $modifierStr = $modifier > 0 ? " (+₱" . number_format($modifier, 2) . ")" : "";
            $expectedFormatted = $option->option_value . $modifierStr;

            if ($expectedFormatted === $formattedValue || strcasecmp($option->option_value, $cleanValue) === 0) {
                return $option;
            }
        }

        // Fallback: search by stripping the modifier suffix
        return self::where('product_id', $productId)
            ->where(function ($q) use ($cleanOptionName) {
                $q->where('option_name', $cleanOptionName)
                  ->orWhereRaw('LOWER(option_name) = ?', [strtolower($cleanOptionName)]);
            })
            ->where(function ($q) use ($cleanValue) {
                $q->where('option_value', $cleanValue)
                  ->orWhereRaw('LOWER(option_value) = ?', [strtolower($cleanValue)]);
            })
            ->first();
    }
}
