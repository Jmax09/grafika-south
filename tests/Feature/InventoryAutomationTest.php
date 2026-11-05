<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductMaterial;
use App\Models\User;
use App\Services\InventoryAutomationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_automatic_bom_material_deduction_and_low_stock_notification(): void
    {
        // 1. Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // 2. Create Customer
        $customer = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => 'password123',
            'role' => 'customer',
            'is_active' => true,
        ]);

        // 3. Create Ink Material (100ml stock, low stock threshold = 30ml)
        $ink = Material::create([
            'name' => 'Sublimation Ink Black',
            'category' => 'Ink',
            'unit' => 'ml',
            'current_stock' => 100.00,
            'low_stock_threshold' => 30.00,
        ]);

        // 4. Create Product with BOM (consumes 8ml ink per unit)
        $product = Product::create([
            'name' => 'T-Shirt Custom Print',
            'slug' => 'tshirt-custom-print',
            'category' => 'Apparel',
            'price' => 250.00,
            'base_price' => 250.00,
            'is_active' => true,
        ]);

        ProductMaterial::create([
            'product_id' => $product->id,
            'material_id' => $ink->id,
            'quantity_used' => 8.0000, // 8ml per shirt
        ]);

        // 5. Create Order for 10 T-Shirts (Total deduction = 10 * 8ml = 80ml)
        // New stock should be 100 - 80 = 20ml (<= 30ml threshold -> triggers alert!)
        $order = Order::create([
            'order_number' => 'TEST-ORD-001',
            'user_id' => $customer->id,
            'status' => 'pending',
            'payment_status' => 'paid',
            'total_price' => 2500.00,
            'total_amount' => 2500.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 250.00,
            'subtotal' => 2500.00,
        ]);

        // 6. Run Inventory Automation Service
        $service = new InventoryAutomationService();
        $service->processOrderDeduction($order);

        // 7. Assertions:
        // Current stock should be 20.00ml
        $this->assertEquals(20.00, (float) $ink->fresh()->current_stock);

        // MaterialLog entry created
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $ink->id,
            'order_id' => $order->id,
            'quantity_deducted' => 80.0000,
            'type' => 'auto_deduction',
        ]);

        // Low stock notification created for Super Admin
        $this->assertDatabaseHas('notifications', [
            'user_id' => $superAdmin->id,
            'type' => 'low_stock_alert',
        ]);
    }

    public function test_checkout_triggers_bom_deduction_in_database_transaction(): void
    {
        $customer = User::create([
            'name' => 'Jane Buyer',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'is_active' => true,
        ]);

        $vinyl = Material::create([
            'name' => 'Vinyl Sticker Sheet',
            'category' => 'Paper',
            'unit' => 'sheets',
            'current_stock' => 50.00,
            'low_stock_threshold' => 10.00,
        ]);

        $product = Product::create([
            'name' => 'Die-Cut Stickers',
            'slug' => 'die-cut-stickers',
            'category' => 'Stickers',
            'price' => 100.00,
            'base_price' => 100.00,
            'is_active' => true,
        ]);

        ProductMaterial::create([
            'product_id' => $product->id,
            'material_id' => $vinyl->id,
            'quantity_used' => 2.0000, // 2 sheets per pack
        ]);

        $this->actingAs($customer);

        // Put item in session cart
        session()->put('cart', [
            'prod_' . $product->id => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => 100.00,
                'quantity' => 5, // Requires 5 * 2 = 10 sheets
            ],
        ]);

        $response = $this->post('/checkout', [
            'payment_method' => 'GCash',
            'notes' => 'Handle with care',
        ]);

        $order = Order::where('user_id', $customer->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('customer.orders.show', $order->id));

        // On checkout, stock is RESERVED (reserved_stock increased), not immediately deducted.
        // Physical current_stock remains at 50 until admin approval.
        $freshVinyl = $vinyl->fresh();
        $this->assertEquals(50.00, (float) $freshVinyl->current_stock);
        $this->assertEquals(10.00, (float) $freshVinyl->reserved_stock);

        // Reservation log recorded with before/after audit values
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $vinyl->id,
            'order_id' => $order->id,
            'quantity_deducted' => 10.0000,
            'stock_before' => 0.0000,   // reserved_before
            'stock_after' => 10.0000,   // reserved_after
            'type' => 'reservation',
        ]);

        // Now simulate admin approval: convert reservation to actual deduction
        $inventoryService = new \App\Services\InventoryAutomationService();
        $inventoryService->convertReservationToDeduction($order->fresh()->load('items.product.materials'));

        // After approval, current_stock is reduced from 50 to 40
        $this->assertEquals(40.00, (float) $vinyl->fresh()->current_stock);
        $this->assertEquals(0.00, (float) $vinyl->fresh()->reserved_stock);

        // auto_deduction log should exist after conversion
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $vinyl->id,
            'order_id' => $order->id,
            'quantity_deducted' => 10.0000,
            'stock_before' => 50.0000,
            'stock_after' => 40.0000,
            'type' => 'auto_deduction',
        ]);

        // Cart is cleared
        $this->assertNull(session('cart'));
    }


    public function test_checkout_rolls_back_entirely_when_bom_stock_is_insufficient(): void
    {
        $customer = User::create([
            'name' => 'Shortage Buyer',
            'email' => 'shortage@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'is_active' => true,
        ]);

        $paper = Material::create([
            'name' => 'Special Cardstock',
            'category' => 'Paper',
            'unit' => 'sheets',
            'current_stock' => 5.00, // Only 5 available
            'low_stock_threshold' => 10.00,
        ]);

        $product = Product::create([
            'name' => 'Wedding Invitation Set',
            'slug' => 'wedding-invitation-set',
            'category' => 'Cards',
            'price' => 500.00,
            'base_price' => 500.00,
            'is_active' => true,
        ]);

        ProductMaterial::create([
            'product_id' => $product->id,
            'material_id' => $paper->id,
            'quantity_used' => 10.0000, // Requires 10 sheets per set
        ]);

        $this->actingAs($customer);

        session()->put('cart', [
            'prod_' . $product->id => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => 500.00,
                'quantity' => 2, // Requires 2 * 10 = 20 sheets (5 available)
            ],
        ]);

        $response = $this->post('/checkout', [
            'payment_method' => 'GCash',
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        // Verify database rolled back: no order, no order items, stock unchanged
        $this->assertDatabaseMissing('orders', ['user_id' => $customer->id]);
        $this->assertDatabaseMissing('material_logs', ['material_id' => $paper->id]);
        $this->assertEquals(5.00, (float) $paper->fresh()->current_stock);

        // Cart is preserved so user doesn't lose their cart
        $this->assertNotNull(session('cart'));
    }

    public function test_json_checkout_returns_422_when_bom_material_stock_is_insufficient(): void
    {
        $customer = User::create([
            'name' => 'API Buyer',
            'email' => 'api_buyer@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'is_active' => true,
        ]);

        $ink = Material::create([
            'name' => 'Sublimation Ink Yellow',
            'category' => 'Ink',
            'unit' => 'ml',
            'current_stock' => 2.00,
            'low_stock_threshold' => 10.00,
        ]);

        $product = Product::create([
            'name' => 'Full Color Poster',
            'slug' => 'full-color-poster',
            'category' => 'Posters',
            'price' => 120.00,
            'base_price' => 120.00,
            'is_active' => true,
        ]);

        ProductMaterial::create([
            'product_id' => $product->id,
            'material_id' => $ink->id,
            'quantity_used' => 5.0000, // 5ml per poster
        ]);

        $this->actingAs($customer);

        session()->put('cart', [
            'prod_' . $product->id => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => 120.00,
                'quantity' => 1, // Requires 5ml (only 2ml available)
            ],
        ]);

        $response = $this->postJson('/checkout', [
            'payment_method' => 'GCash',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'error' => 'insufficient_stock',
        ]);
        $response->assertJsonFragment([
            'message' => 'Order failed. Raw material [Sublimation Ink Yellow] is out of stock.',
        ]);

        // Verify zero database changes
        $this->assertDatabaseMissing('orders', ['user_id' => $customer->id]);
        $this->assertEquals(2.00, (float) $ink->fresh()->current_stock);
    }

    public function test_multi_item_aggregate_bom_check_prevents_checkout(): void
    {
        $customer = User::create([
            'name' => 'Multi Item Buyer',
            'email' => 'multi_buyer@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'is_active' => true,
        ]);

        $laminate = Material::create([
            'name' => 'Gloss Film 125mic',
            'category' => 'Film',
            'unit' => 'sheets',
            'current_stock' => 10.00, // 10 sheets available
            'low_stock_threshold' => 5.00,
        ]);

        $prodA = Product::create([
            'name' => 'Laminated Menu Small',
            'slug' => 'laminated-menu-small',
            'category' => 'Menus',
            'price' => 80.00,
            'base_price' => 80.00,
            'is_active' => true,
        ]);
        ProductMaterial::create([
            'product_id' => $prodA->id,
            'material_id' => $laminate->id,
            'quantity_used' => 1.0000, // 1 sheet each
        ]);

        $prodB = Product::create([
            'name' => 'Laminated Menu Large',
            'slug' => 'laminated-menu-large',
            'category' => 'Menus',
            'price' => 150.00,
            'base_price' => 150.00,
            'is_active' => true,
        ]);
        ProductMaterial::create([
            'product_id' => $prodB->id,
            'material_id' => $laminate->id,
            'quantity_used' => 2.0000, // 2 sheets each
        ]);

        $this->actingAs($customer);

        // Cart: 6 of ProdA (6 sheets) + 3 of ProdB (6 sheets) = 12 sheets required (only 10 available)
        session()->put('cart', [
            'prod_' . $prodA->id => [
                'product_id' => $prodA->id,
                'name' => $prodA->name,
                'price' => 80.00,
                'quantity' => 6,
            ],
            'prod_' . $prodB->id => [
                'product_id' => $prodB->id,
                'name' => $prodB->name,
                'price' => 150.00,
                'quantity' => 3,
            ],
        ]);

        $response = $this->postJson('/checkout', [
            'payment_method' => 'GCash',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'message' => 'Order failed. Raw material [Gloss Film 125mic] is out of stock.',
        ]);

        $this->assertEquals(10.00, (float) $laminate->fresh()->current_stock);
    }

    public function test_option_level_bom_is_added_to_product_bom_on_checkout_and_deducted(): void
    {
        $customer = User::create([
            'name' => 'Option Buyer',
            'email' => 'opt_buyer@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'is_active' => true,
        ]);

        $ink = Material::create([
            'name' => 'Sublimation Ink Red',
            'category' => 'Ink',
            'unit' => 'ml',
            'current_stock' => 100.00,
            'low_stock_threshold' => 10.00,
        ]);

        $fabric = Material::create([
            'name' => 'Cotton Fabric Blue',
            'category' => 'Fabric',
            'unit' => 'sheets',
            'current_stock' => 50.00,
            'low_stock_threshold' => 5.00,
        ]);

        $product = Product::create([
            'name' => 'Custom Jersey',
            'slug' => 'custom-jersey',
            'category' => 'Apparel',
            'price' => 300.00,
            'base_price' => 300.00,
            'is_active' => true,
        ]);

        // Base Product BOM (requires 5ml ink)
        ProductMaterial::create([
            'product_id' => $product->id,
            'material_id' => $ink->id,
            'quantity_used' => 5.0000,
        ]);

        // Option (Size: L (+₱50.00))
        $option = \App\Models\ProductOption::create([
            'product_id' => $product->id,
            'option_name' => 'Size',
            'option_value' => 'L',
            'price_modifier' => 50.00,
        ]);

        // Option BOM (requires 2 meters fabric)
        \App\Models\ProductOptionMaterial::create([
            'product_option_id' => $option->id,
            'material_id' => $fabric->id,
            'quantity_used' => 2.0000,
        ]);

        $this->actingAs($customer);

        // Put in cart
        session()->put('cart', [
            'prod_' . $product->id . '_hash' => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => 350.00,
                'quantity' => 10, // Requires 10 * 5ml = 50ml ink, 10 * 2m = 20m fabric
                'options' => [
                    'Size' => 'L (+₱50.00)',
                ],
            ],
        ]);

        $response = $this->post('/checkout', [
            'payment_method' => 'GCash',
        ]);

        $order = Order::where('user_id', $customer->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('customer.orders.show', $order->id));

        // On checkout, materials are RESERVED not immediately deducted.
        // current_stock stays unchanged; reserved_stock increases.
        $this->assertEquals(100.00, (float) $ink->fresh()->current_stock);
        $this->assertEquals(50.00, (float) $fabric->fresh()->current_stock);
        $this->assertEquals(50.00, (float) $ink->fresh()->reserved_stock);
        $this->assertEquals(20.00, (float) $fabric->fresh()->reserved_stock);

        // Reservation logs exist for both materials
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $ink->id,
            'order_id' => $order->id,
            'quantity_deducted' => 50.0000,
            'type' => 'reservation',
        ]);

        $this->assertDatabaseHas('material_logs', [
            'material_id' => $fabric->id,
            'order_id' => $order->id,
            'quantity_deducted' => 20.0000,
            'type' => 'reservation',
        ]);

        // Simulate admin approval: convert reservation to actual deduction
        $inventoryService = new \App\Services\InventoryAutomationService();
        $inventoryService->convertReservationToDeduction($order->fresh()->load('items.product.materials', 'items.product.options.materials'));

        // Verify stock deductions after approval
        $this->assertEquals(50.00, (float) $ink->fresh()->current_stock); // 100 - 50 = 50ml remaining
        $this->assertEquals(30.00, (float) $fabric->fresh()->current_stock); // 50 - 20 = 30m remaining

        // Verify auto_deduction logs
        $this->assertDatabaseHas('material_logs', [
            'material_id' => $ink->id,
            'order_id' => $order->id,
            'quantity_deducted' => 50.0000,
        ]);

        $this->assertDatabaseHas('material_logs', [
            'material_id' => $fabric->id,
            'order_id' => $order->id,
            'quantity_deducted' => 20.0000,
        ]);

    }

    public function test_option_level_bom_insufficient_stock_prevents_checkout(): void
    {
        $customer = User::create([
            'name' => 'Option OutOfStock Buyer',
            'email' => 'opt_oos_buyer@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'is_active' => true,
        ]);

        $ink = Material::create([
            'name' => 'Sublimation Ink Green',
            'category' => 'Ink',
            'unit' => 'ml',
            'current_stock' => 100.00,
            'low_stock_threshold' => 10.00,
        ]);

        $fabric = Material::create([
            'name' => 'Silk Fabric Gold',
            'category' => 'Fabric',
            'unit' => 'sheets',
            'current_stock' => 5.00, // Only 5 meters available
            'low_stock_threshold' => 5.00,
        ]);

        $product = Product::create([
            'name' => 'Luxury Cape',
            'slug' => 'luxury-cape',
            'category' => 'Apparel',
            'price' => 1000.00,
            'base_price' => 1000.00,
            'is_active' => true,
        ]);

        // Base Product BOM (requires 5ml ink)
        ProductMaterial::create([
            'product_id' => $product->id,
            'material_id' => $ink->id,
            'quantity_used' => 5.0000,
        ]);

        // Option (Size: XL (+₱100.00))
        $option = \App\Models\ProductOption::create([
            'product_id' => $product->id,
            'option_name' => 'Size',
            'option_value' => 'XL',
            'price_modifier' => 100.00,
        ]);

        // Option BOM (requires 2 meters fabric)
        \App\Models\ProductOptionMaterial::create([
            'product_option_id' => $option->id,
            'material_id' => $fabric->id,
            'quantity_used' => 2.0000,
        ]);

        $this->actingAs($customer);

        // Put in cart (qty = 3 -> requires 3 * 2m = 6m fabric, only 5m available)
        session()->put('cart', [
            'prod_' . $product->id . '_hash' => [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => 1100.00,
                'quantity' => 3,
                'options' => [
                    'Size' => 'XL (+₱100.00)',
                ],
            ],
        ]);

        $response = $this->post('/checkout', [
            'payment_method' => 'GCash',
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        // Verify database rolled back: no order, stocks unchanged
        $this->assertDatabaseMissing('orders', ['user_id' => $customer->id]);
        $this->assertEquals(100.00, (float) $ink->fresh()->current_stock);
        $this->assertEquals(5.00, (float) $fabric->fresh()->current_stock);
    }
}

