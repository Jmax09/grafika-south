<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOptionMaterial extends Model
{
    use HasFactory;

    protected $table = 'product_option_materials';

    protected $fillable = [
        'product_option_id',
        'material_id',
        'quantity_used',
    ];

    protected $casts = [
        'quantity_used' => 'decimal:4',
    ];

    public function productOption(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
