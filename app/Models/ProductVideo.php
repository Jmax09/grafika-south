<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'video_path',
        'title',
        'sort_order',
        'position',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->video_path);
    }

    /**
     * Lowercase file extension (mp4, webm, mov, avi) — used to pick the
     * correct <source type> hint on the customer-facing player.
     */
    public function getExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->video_path, PATHINFO_EXTENSION));
    }
}
