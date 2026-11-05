<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductsCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_displays_empty_state_message_when_catalog_is_empty()
    {
        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('No products found');
    }

    public function test_displays_dynamic_products_and_db_category_counts()
    {
        Product::create([
            'name' => 'Heavy Outdoor Banner 13oz',
            'slug' => 'heavy-outdoor-banner-13oz',
            'category' => 'large-format',
            'description' => 'Solvent print heavy duty tarpaulin.',
            'price' => 450.00,
            'base_price' => 450.00,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Glossy Business Cards 300gsm',
            'slug' => 'glossy-business-cards-300gsm',
            'category' => 'marketing',
            'description' => 'Double sided premium business cards.',
            'price' => 200.00,
            'base_price' => 200.00,
            'is_active' => true,
        ]);

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('Heavy Outdoor Banner 13oz');
        $response->assertSee('Glossy Business Cards 300gsm');
        $response->assertSee('₱450.00');
        $response->assertSee('₱200.00');
    }

    public function test_displays_out_of_stock_badge_when_bom_raw_materials_are_exhausted()
    {
        $material = \App\Models\Material::create([
            'name' => 'Sublimation Ink Cyan',
            'category' => 'Ink',
            'unit' => 'ml',
            'current_stock' => 0.00, // Exhausted stock
            'low_stock_threshold' => 10.00,
        ]);

        $product = Product::create([
            'name' => 'Custom Mug Print',
            'slug' => 'custom-mug-print',
            'category' => 'Souvenirs',
            'description' => 'Ceramic mug print.',
            'price' => 150.00,
            'base_price' => 150.00,
            'is_active' => true,
        ]);

        \App\Models\ProductMaterial::create([
            'product_id' => $product->id,
            'material_id' => $material->id,
            'quantity_used' => 5.0000,
        ]);

        $this->assertFalse($product->fresh()->has_sufficient_stock);
        $this->assertEquals(0, $product->fresh()->max_producible_quantity);

        // Catalog index check
        $response = $this->get(route('products.index'));
        $response->assertStatus(200);
        $response->assertSee('Out of Stock');

        // Product show check
        $showResponse = $this->get(route('products.show', $product->slug));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Out of Stock — Cannot Order');
    }

    public function test_cart_add_rejects_out_of_stock_bom_product()
    {
        $material = \App\Models\Material::create([
            'name' => 'Lanyard Strap 1-inch',
            'category' => 'Accessories',
            'unit' => 'pcs',
            'current_stock' => 2.00, // Only 2 available
            'low_stock_threshold' => 5.00,
        ]);

        $product = Product::create([
            'name' => 'ID Lanyard Set',
            'slug' => 'id-lanyard-set',
            'category' => 'Identification',
            'price' => 45.00,
            'base_price' => 45.00,
            'is_active' => true,
        ]);

        \App\Models\ProductMaterial::create([
            'product_id' => $product->id,
            'material_id' => $material->id,
            'quantity_used' => 1.0000,
        ]);

        // Attempting to add 5 units (when only 2 available)
        $response = $this->from(route('products.show', $product->slug))->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response->assertRedirect(route('products.show', $product->slug));
        $response->assertSessionHas('error');
        $this->assertNull(session('cart'));
    }

    public function test_product_search_by_name_and_description(): void
    {
        Product::create([
            'name' => 'Custom Sublimation T-Shirt',
            'slug' => 'custom-sublimation-t-shirt',
            'category' => 'Apparel',
            'description' => 'Vibrant CMYK sublimation on polyester fabric.',
            'price' => 250.00,
            'base_price' => 250.00,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'PVC ID Card Printing',
            'slug' => 'pvc-id-card-printing',
            'category' => 'Identification',
            'description' => '30mil waterproof PVC employee badges.',
            'price' => 50.00,
            'base_price' => 50.00,
            'is_active' => true,
        ]);

        // Search for Sublimation
        $response = $this->get(route('products.index', ['q' => 'Sublimation']));
        $response->assertStatus(200);
        $response->assertSee(route('products.show', 'custom-sublimation-t-shirt'));
        $response->assertDontSee(route('products.show', 'pvc-id-card-printing'));

        // Search for PVC
        $response2 = $this->get(route('products.index', ['q' => 'PVC']));
        $response2->assertStatus(200);
        $response2->assertSee(route('products.show', 'pvc-id-card-printing'));
        $response2->assertDontSee(route('products.show', 'custom-sublimation-t-shirt'));
    }

    public function test_navbar_contains_global_product_search(): void
    {
        $response = $this->get(route('landing'));
        $response->assertStatus(200);
        $response->assertSee('navbar-search-form', false);
        $response->assertSee('Search products', false);
    }
}
