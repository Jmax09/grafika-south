<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DynamicFeaturedProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_displays_dynamic_products_when_products_exist_in_database()
    {
        $product = Product::create([
            'name' => 'Custom Vinyl Banner Print',
            'slug' => 'custom-vinyl-banner-print',
            'category' => 'Apparel',
            'description' => 'High quality outdoor vinyl banner.',
            'price' => 599.00,
            'base_price' => 599.00,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Custom Vinyl Banner Print');
        $response->assertSee('₱599.00');
    }
}
