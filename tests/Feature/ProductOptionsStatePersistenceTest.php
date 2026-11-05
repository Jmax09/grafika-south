<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Product;
use App\Models\ProductMaterial;
use App\Models\ProductOption;
use App\Models\ProductOptionMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductOptionsStatePersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@grafikasouth.com',
            'password' => bcrypt('secret123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_modifying_form_fields_retains_dynamic_options_and_inherited_bom()
    {
        $material = Material::create([
            'name' => 'Vinyl Sticker Sheet',
            'category' => 'Paper',
            'unit' => 'sheets',
            'current_stock' => 100,
            'low_stock_threshold' => 10,
        ]);

        $product = Product::create([
            'name' => 'Custom Vinyl Stickers',
            'slug' => 'custom-vinyl-stickers',
            'category' => 'Stickers',
            'description' => 'Original description',
            'price' => 100.00,
            'base_price' => 100.00,
            'is_active' => true,
        ]);

        ProductMaterial::create([
            'product_id' => $product->id,
            'material_id' => $material->id,
            'quantity_used' => 1.0000,
        ]);

        $option = ProductOption::create([
            'product_id' => $product->id,
            'option_name' => 'Size',
            'option_value' => 'A4',
            'price_modifier' => 20.00,
        ]);

        $this->assertCount(0, $option->materials);

        $response = $this->actingAs($this->superAdmin)->postJson(route('super-admin.products.master-save', $product->id), [
            'name' => 'Custom Vinyl Stickers Updated',
            'category' => 'Stickers',
            'description' => 'Updated description',
            'price' => 120.00,
            'is_active' => 1,
            'materials' => [
                [
                    'material_id' => $material->id,
                    'quantity_used' => 2.0000,
                ],
            ],
            'options_present' => 1,
            'options' => [
                [
                    'id' => $option->id,
                    'option_name' => 'Size',
                    'option_value' => 'A4',
                    'price_modifier' => 20.00,
                    'has_custom_bom' => '0',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $responseData = $response->json();
        $this->assertCount(1, $responseData['options']);
        $this->assertEquals('A4', $responseData['options'][0]['option_value']);
        $this->assertFalse($responseData['options'][0]['has_custom_bom']);
        $this->assertCount(1, $responseData['options'][0]['materials']);
        $this->assertEquals(2.0, (float)$responseData['options'][0]['materials'][0]['quantity_used']);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Custom Vinyl Stickers Updated',
            'price' => 120.00,
        ]);

        $this->assertDatabaseHas('product_options', [
            'id' => $option->id,
            'option_value' => 'A4',
        ]);

        $this->assertDatabaseMissing('product_option_materials', [
            'product_option_id' => $option->id,
        ]);
    }

    public function test_custom_bom_option_persists_custom_materials_independently()
    {
        $material1 = Material::create([
            'name' => 'T-Shirt White',
            'category' => 'Apparel',
            'unit' => 'pcs',
            'current_stock' => 50,
            'low_stock_threshold' => 5,
        ]);

        $material2 = Material::create([
            'name' => 'Sublimation Ink Black',
            'category' => 'Ink',
            'unit' => 'ml',
            'current_stock' => 500,
            'low_stock_threshold' => 50,
        ]);

        $product = Product::create([
            'name' => 'Printed Shirt',
            'slug' => 'printed-shirt',
            'category' => 'Apparel',
            'price' => 250.00,
            'base_price' => 250.00,
            'is_active' => true,
        ]);

        ProductMaterial::create([
            'product_id' => $product->id,
            'material_id' => $material1->id,
            'quantity_used' => 1.0000,
        ]);

        $response = $this->actingAs($this->superAdmin)->postJson(route('super-admin.products.master-save', $product->id), [
            'name' => 'Printed Shirt',
            'category' => 'Apparel',
            'price' => 250.00,
            'is_active' => 1,
            'materials' => [
                [
                    'material_id' => $material1->id,
                    'quantity_used' => 1.0000,
                ],
            ],
            'options_present' => 1,
            'options' => [
                [
                    'id' => null,
                    'option_name' => 'Size',
                    'option_value' => 'M',
                    'price_modifier' => 0.00,
                    'has_custom_bom' => '0',
                ],
                [
                    'id' => null,
                    'option_name' => 'Size',
                    'option_value' => 'XXL',
                    'price_modifier' => 50.00,
                    'has_custom_bom' => '1',
                    'materials' => [
                        [
                            'material_id' => $material2->id,
                            'quantity_used' => 10.0000,
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200);
        $options = $response->json('options');
        $this->assertCount(2, $options);

        $optM = collect($options)->firstWhere('option_value', 'M');
        $optXXL = collect($options)->firstWhere('option_value', 'XXL');

        $this->assertFalse($optM['has_custom_bom']);
        $this->assertEquals('T-Shirt White', $optM['materials'][0]['material_name']);

        $this->assertTrue($optXXL['has_custom_bom']);
        $this->assertEquals('Sublimation Ink Black', $optXXL['materials'][0]['material_name']);
        $this->assertEquals(10.0, (float)$optXXL['materials'][0]['quantity_used']);

        $xxlDb = ProductOption::where('product_id', $product->id)->where('option_value', 'XXL')->first();
        $this->assertNotNull($xxlDb);
        $this->assertDatabaseHas('product_option_materials', [
            'product_option_id' => $xxlDb->id,
            'material_id' => $material2->id,
            'quantity_used' => 10.0000,
        ]);
    }

    public function test_adjusting_main_bom_recipe_does_not_clear_or_delete_existing_dynamic_options()
    {
        $mat1 = Material::create([
            'name' => 'yellow',
            'category' => 'Ink',
            'unit' => 'ml',
            'current_stock' => 100,
            'low_stock_threshold' => 10,
        ]);

        $mat2 = Material::create([
            'name' => 'jamc',
            'category' => 'Liquid',
            'unit' => 'pcs',
            'current_stock' => 100,
            'low_stock_threshold' => 10,
        ]);

        $mat3 = Material::create([
            'name' => 'Plastic',
            'category' => 'Raw',
            'unit' => 'pcs',
            'current_stock' => 50,
            'low_stock_threshold' => 5,
        ]);

        $product = Product::create([
            'name' => 'Custom Item',
            'slug' => 'custom-item',
            'category' => 'Special',
            'price' => 150.00,
            'base_price' => 150.00,
            'is_active' => true,
        ]);

        ProductMaterial::create([
            'product_id' => $product->id,
            'material_id' => $mat1->id,
            'quantity_used' => 1.0000,
        ]);

        $opt = ProductOption::create([
            'product_id' => $product->id,
            'option_name' => 'FINISH',
            'option_value' => 'stantard',
            'price_modifier' => 0.00,
        ]);

        // Super Admin adjusts the BOM in Section 2 (adds mat2 and replaces mat1 with mat3)
        $response = $this->actingAs($this->superAdmin)->postJson(route('super-admin.products.master-save', $product->id), [
            'name' => 'Custom Item',
            'category' => 'Special',
            'price' => 150.00,
            'is_active' => 1,
            'materials' => [
                [
                    'material_id' => $mat3->id,
                    'quantity_used' => 2.0000,
                ],
                [
                    'material_id' => $mat2->id,
                    'quantity_used' => 1.0000,
                ],
            ],
            'options_present' => 1,
            'options' => [
                [
                    'id' => $opt->id,
                    'option_name' => 'FINISH',
                    'option_value' => 'stantard',
                    'price_modifier' => 0.00,
                    'has_custom_bom' => '0',
                ],
            ],
        ]);

        $response->assertStatus(200);
        $options = $response->json('options');
        $this->assertCount(1, $options);
        $this->assertEquals('FINISH', $options[0]['option_name']);
        $this->assertEquals('stantard', $options[0]['option_value']);

        // Check that option still exists in DB
        $this->assertDatabaseHas('product_options', [
            'id' => $opt->id,
            'product_id' => $product->id,
            'option_name' => 'FINISH',
            'option_value' => 'stantard',
        ]);

        // Check that new BOM materials are saved in DB
        $this->assertDatabaseHas('product_materials', [
            'product_id' => $product->id,
            'material_id' => $mat3->id,
            'quantity_used' => 2.0000,
        ]);
        $this->assertDatabaseHas('product_materials', [
            'product_id' => $product->id,
            'material_id' => $mat2->id,
            'quantity_used' => 1.0000,
        ]);
        $this->assertDatabaseMissing('product_materials', [
            'product_id' => $product->id,
            'material_id' => $mat1->id,
        ]);
    }

    public function test_options_not_wiped_when_options_array_is_absent_from_form_data()
    {
        $mat = Material::create([
            'name' => 'Canvas Roll',
            'category' => 'Paper',
            'unit' => 'sheets',
            'current_stock' => 200,
            'low_stock_threshold' => 10,
        ]);

        $product = Product::create([
            'name' => 'Canvas Print',
            'slug' => 'canvas-print',
            'category' => 'Prints',
            'price' => 350.00,
            'base_price' => 350.00,
            'is_active' => true,
        ]);

        ProductMaterial::create([
            'product_id' => $product->id,
            'material_id' => $mat->id,
            'quantity_used' => 1.0,
        ]);

        $opt = ProductOption::create([
            'product_id' => $product->id,
            'option_name' => 'Finish',
            'option_value' => 'Matte',
            'price_modifier' => 30.00,
        ]);

        // Simulate a BOM-only save: options[] is completely absent from FormData
        // but options_present is 1 — this is the exact scenario that previously wiped options
        $response = $this->actingAs($this->superAdmin)->postJson(route('super-admin.products.master-save', $product->id), [
            'name' => 'Canvas Print',
            'category' => 'Prints',
            'price' => 350.00,
            'is_active' => 1,
            'materials' => [
                ['material_id' => $mat->id, 'quantity_used' => 2.5],
            ],
            'options_present' => '1',
            // NOTE: 'options' key is intentionally absent to simulate FormData serialization failure
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // The option MUST still exist in the database
        $this->assertDatabaseHas('product_options', [
            'id' => $opt->id,
            'product_id' => $product->id,
            'option_name' => 'Finish',
            'option_value' => 'Matte',
        ]);
    }
}

