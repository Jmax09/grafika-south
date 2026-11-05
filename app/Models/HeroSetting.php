<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'badge_text',
        'title',
        'highlight_text_1',
        'highlight_text_2',
        'subtitle',
        'button_text',
        'button_url',
        'secondary_button_text',
        'secondary_button_url',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get default instance or first record from database.
     */
    public static function instance(): self
    {
        $setting = self::first();

        if (!$setting) {
            $setting = self::create([
                'badge_text' => 'Commercial CMYK Printing Solutions & Automated BOM Engine',
                'title' => 'Custom Print Shop CMYK E-Commerce & Ordering',
                'highlight_text_1' => 'CMYK E-Commerce',
                'highlight_text_2' => 'Ordering',
                'subtitle' => 'Order custom T-Shirts, PVC ID Cards, Premium Business Cards, and Document Lamination. High-definition CMYK color precision, real-time artwork review, and automated stock deduction.',
                'button_text' => 'Browse Catalog & Order Now',
                'button_url' => route('products.index'),
                'secondary_button_text' => 'Create Customer Account',
                'secondary_button_url' => route('register'),
                'is_active' => true,
            ]);
        }

        return $setting;
    }
}
