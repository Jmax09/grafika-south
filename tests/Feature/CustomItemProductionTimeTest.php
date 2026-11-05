<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomItemProductionTimeTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $superAdmin;
    protected PaymentMethod $gcash;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->gcash = PaymentMethod::create([
            'name' => 'GCash',
            'code' => 'gcash',
            'type' => 'digital',
            'account_name' => 'Grafika South',
            'account_number' => '09171234567',
            'is_active' => true,
            'is_online_payment' => true,
        ]);
    }

    /** @test */
    public function format_minutes_produces_human_readable_strings()
    {
        $this->assertNull(Product::formatMinutes(null));
        $this->assertNull(Product::formatMinutes(0));
        $this->assertEquals('1 min', Product::formatMinutes(1));
        $this->assertEquals('30 mins', Product::formatMinutes(30));
        $this->assertEquals('1 hr', Product::formatMinutes(60));
        $this->assertEquals('1 hr 30 mins', Product::formatMinutes(90));
        $this->assertEquals('2 hrs', Product::formatMinutes(120));
        $this->assertEquals('1 day', Product::formatMinutes(1440));
        $this->assertEquals('1 day 2 hrs', Product::formatMinutes(1560));
    }

    /** @test */
    public function product_estimated_production_time_combines_minimum_and_bom()
    {
        $product = Product::create([
            'name' => 'Custom Mug',
            'slug' => 'custom-mug',
            'category' => 'Merchandise',
            'price' => 150.00,
            'base_price' => 150.00,
            'is_active' => true,
            'requires_design_file' => true,
            'custom_production_time_minutes' => 30,
        ]);

        $this->assertEquals(30, $product->getEstimatedProductionTime());
        $this->assertEquals('30 mins', $product->formatted_production_time);

        // Add a BOM component with 45 mins processing time
        $sublimationInk = Material::create([
            'name' => 'Sublimation Ink',
            'category' => 'Ink',
            'unit' => 'ml',
            'current_stock' => 500,
            'low_stock_threshold' => 50,
            'production_time_minutes' => 45,
        ]);

        $product->materials()->attach($sublimationInk->id, ['quantity_used' => 5]);
        $product->load('materials');

        // Should return max(30, 45) = 45 mins
        $this->assertEquals(45, $product->getEstimatedProductionTime());
    }

    /** @test */
    public function regular_product_has_no_production_time_applied()
    {
        $regularProduct = Product::create([
            'name' => 'Standard Plain T-Shirt',
            'slug' => 'standard-plain-t-shirt',
            'category' => 'Apparel',
            'price' => 200.00,
            'base_price' => 200.00,
            'is_active' => true,
            'requires_design_file' => false,
            'requires_custom_terms' => false,
            'custom_production_time_minutes' => null,
        ]);

        $this->assertFalse($regularProduct->is_customizable);
        $this->assertNull($regularProduct->formatted_production_time);
    }

    /** @test */
    public function adding_custom_item_to_cart_stores_production_time()
    {
        $customProduct = Product::create([
            'name' => 'Custom Polo Shirt',
            'slug' => 'custom-polo-shirt',
            'category' => 'Apparel',
            'price' => 350.00,
            'base_price' => 350.00,
            'is_active' => true,
            'requires_design_file' => false,
            'requires_custom_terms' => true,
            'custom_production_time_minutes' => 60,
        ]);

        $response = $this->actingAs($this->customer)->post(route('cart.add'), [
            'product_id' => $customProduct->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('cart.index'));

        $cart = session()->get('cart', []);
        $this->assertNotEmpty($cart);
        $item = reset($cart);

        // Cart stores the total production time scaled by quantity (60 min/unit × 2 units = 120 mins)
        $this->assertEquals(120, $item['production_time_minutes']);
        $this->assertEquals('2 hrs', $item['formatted_production_time']);
    }

    /** @test */
    public function custom_order_stores_estimated_production_minutes_on_order_and_items()
    {
        Storage::fake('public');

        $customProduct = Product::create([
            'name' => 'Custom Tarpaulin Banner',
            'slug' => 'custom-tarpaulin-banner',
            'category' => 'Marketing',
            'price' => 500.00,
            'base_price' => 500.00,
            'is_active' => true,
            'requires_design_file' => true,
            'custom_production_time_minutes' => 120,
        ]);

        $proofFile = UploadedFile::fake()->create('proof.jpg', 100, 'image/jpeg');

        $cart = [
            'item_1' => [
                'cart_key' => 'item_1',
                'product_id' => $customProduct->id,
                'name' => $customProduct->name,
                'price' => 500.00,
                'quantity' => 1,
                'requires_design_file' => true,
                'requires_custom_terms' => false,
                'production_time_minutes' => 120,
                'formatted_production_time' => '2 hrs',
                'design_file_path' => 'order_files_temp/artwork.png',
                'selected' => true,
            ],
        ];

        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->post(route('checkout.store'), [
                'fulfillment_type' => 'delivery',
                'payment_method' => 'GCash',
                'payment_plan' => 'full',
                'proof_of_payment' => $proofFile,
            ]);

        $order = Order::where('is_custom_order', true)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('customer.orders.show', $order->id));
        $this->assertEquals(120, $order->estimated_production_minutes);
        $this->assertEquals('2 hrs', $order->formatted_production_time);

        $orderItem = OrderItem::where('order_id', $order->id)->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals(120, $orderItem->production_time_minutes);
        $this->assertEquals('2 hrs', $orderItem->formatted_production_time);
    }

    /** @test */
    public function super_admin_can_update_custom_production_time_minutes_on_product()
    {
        $product = Product::create([
            'name' => 'Custom Tote Bag',
            'slug' => 'custom-tote-bag',
            'category' => 'Apparel',
            'price' => 120.00,
            'base_price' => 120.00,
            'is_active' => true,
            'requires_design_file' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->put(route('super-admin.products.update', $product->id), [
            'name' => 'Custom Tote Bag Updated',
            'category' => 'Apparel',
            'price' => 120.00,
            'is_active' => 1,
            'custom_production_time_minutes' => 45,
            'requires_design_file' => 1,
        ]);

        $response->assertRedirect(route('super-admin.products.edit', $product->id));

        $product->refresh();
        $this->assertEquals(45, $product->custom_production_time_minutes);
        $this->assertEquals('45 mins', $product->formatted_production_time);
    }

    /** @test */
    public function super_admin_product_edit_page_renders_production_time_input_field()
    {
        $product = Product::create([
            'name' => 'Custom Jersey',
            'slug' => 'custom-jersey',
            'category' => 'Apparel',
            'price' => 300.00,
            'base_price' => 300.00,
            'is_active' => true,
            'requires_design_file' => true,
            'custom_production_time_minutes' => 90,
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('super-admin.products.edit', $product->id));

        $response->assertOk();
        $response->assertSee('custom_production_time_minutes');
        $response->assertSee('Estimated Production Time (Minutes) — Customized Items Only');
        $response->assertSee('value="90"', false);
        $response->assertSee('1 hr 30 mins');
    }

    /** @test */
    public function super_admin_can_update_production_time_via_master_save()
    {
        $product = Product::create([
            'name' => 'Custom Signage',
            'slug' => 'custom-signage',
            'category' => 'Marketing',
            'price' => 450.00,
            'base_price' => 450.00,
            'is_active' => true,
            'requires_design_file' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.products.master-save', $product->id), [
            'name' => 'Custom Signage Updated',
            'category' => 'Marketing',
            'price' => 450.00,
            'is_active' => 1,
            'requires_design_file' => 1,
            'custom_production_time_minutes' => 60,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertEquals(60, $product->fresh()->custom_production_time_minutes);
    }

    /** @test */
    public function admin_can_set_production_time_on_raw_materials()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $material = Material::create([
            'name' => 'Special Glitter Vinyl',
            'category' => 'Vinyl',
            'unit' => 'sheets',
            'current_stock' => 100,
            'low_stock_threshold' => 20,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.materials.update', $material->id), [
            'name' => 'Special Glitter Vinyl Updated',
            'category' => 'Vinyl',
            'unit' => 'sheets',
            'current_stock' => 100,
            'low_stock_threshold' => 20,
            'production_time_minutes' => 25,
        ]);

        $response->assertRedirect();
        $this->assertEquals(25, $material->fresh()->production_time_minutes);
    }
}

