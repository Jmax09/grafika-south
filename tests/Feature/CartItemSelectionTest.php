<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartItemSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_cart_items_default_to_selected_when_added()
    {
        $product = Product::where('is_active', true)->first();

        $response = $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('cart.index'));
        $cart = session('cart');
        $this->assertNotEmpty($cart);

        $firstItem = reset($cart);
        $this->assertTrue($firstItem['selected']);
    }

    public function test_cart_page_renders_item_checkboxes_and_select_all()
    {
        $products = Product::where('is_active', true)->take(2)->get();

        $cart = [
            'key_1' => [
                'cart_key' => 'key_1',
                'product_id' => $products[0]->id,
                'name' => $products[0]->name,
                'price' => 150.00,
                'quantity' => 2,
                'selected' => true,
            ],
            'key_2' => [
                'cart_key' => 'key_2',
                'product_id' => $products[1]->id,
                'name' => $products[1]->name,
                'price' => 200.00,
                'quantity' => 1,
                'selected' => false,
            ],
        ];

        $response = $this->withSession(['cart' => $cart])->get(route('cart.index'));

        $response->assertStatus(200);
        $response->assertSee('Select All');
        $response->assertSee('checkbox-item-key_1');
        $response->assertSee('checkbox-item-key_2');
        $response->assertSee('selected-items-count');
        $response->assertSee('items selected');
        $response->assertSee('300.00'); // Subtotal for key_1 only (150 * 2)
    }

    public function test_toggle_select_endpoint_updates_item_selection_and_recalculates_subtotal()
    {
        $cart = [
            'key_1' => [
                'cart_key' => 'key_1',
                'product_id' => 1,
                'name' => 'Item 1',
                'price' => 100.00,
                'quantity' => 2,
                'selected' => true,
            ],
            'key_2' => [
                'cart_key' => 'key_2',
                'product_id' => 2,
                'name' => 'Item 2',
                'price' => 50.00,
                'quantity' => 1,
                'selected' => true,
            ],
        ];

        // Deselect key_1
        $response = $this->withSession(['cart' => $cart])->postJson(route('cart.toggle-select'), [
            'key' => 'key_1',
            'selected' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'key' => 'key_1',
            'selected' => false,
            'selected_count' => 1,
            'subtotal' => 50.00,
            'formatted_subtotal' => '₱50.00',
        ]);

        $sessionCart = session('cart');
        $this->assertFalse($sessionCart['key_1']['selected']);
        $this->assertTrue($sessionCart['key_2']['selected']);
    }

    public function test_select_all_endpoint_updates_all_items_selection()
    {
        $cart = [
            'key_1' => [
                'cart_key' => 'key_1',
                'product_id' => 1,
                'name' => 'Item 1',
                'price' => 100.00,
                'quantity' => 2,
                'selected' => true,
            ],
            'key_2' => [
                'cart_key' => 'key_2',
                'product_id' => 2,
                'name' => 'Item 2',
                'price' => 50.00,
                'quantity' => 1,
                'selected' => true,
            ],
        ];

        // Deselect all
        $response = $this->withSession(['cart' => $cart])->postJson(route('cart.select-all'), [
            'selected' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'selected' => false,
            'selected_count' => 0,
            'subtotal' => 0.00,
            'formatted_subtotal' => '₱0.00',
        ]);

        $sessionCart = session('cart');
        $this->assertFalse($sessionCart['key_1']['selected']);
        $this->assertFalse($sessionCart['key_2']['selected']);

        // Select all
        $response2 = $this->withSession(['cart' => $sessionCart])->postJson(route('cart.select-all'), [
            'selected' => true,
        ]);

        $response2->assertStatus(200);
        $response2->assertJson([
            'success' => true,
            'selected' => true,
            'selected_count' => 2,
            'subtotal' => 250.00,
            'formatted_subtotal' => '₱250.00',
        ]);
    }

    public function test_checkout_filters_only_selected_items_and_shows_warning_when_none_selected()
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $product1 = Product::where('is_active', true)->first();
        $product2 = Product::where('is_active', true)->skip(1)->first() ?? $product1;

        $cart = [
            'key_1' => [
                'cart_key' => 'key_1',
                'product_id' => $product1->id,
                'name' => 'Selected Product',
                'price' => 100.00,
                'quantity' => 1,
                'selected' => true,
            ],
            'key_2' => [
                'cart_key' => 'key_2',
                'product_id' => $product2->id,
                'name' => 'Unselected Product',
                'price' => 500.00,
                'quantity' => 1,
                'selected' => false,
            ],
        ];

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('Selected Product');
        $response->assertDontSee('Unselected Product');
        $response->assertSee('₱100.00');

        // All items unselected should redirect back to cart
        $cartUnselected = [
            'key_1' => [
                'cart_key' => 'key_1',
                'product_id' => $product1->id,
                'name' => 'Selected Product',
                'price' => 100.00,
                'quantity' => 1,
                'selected' => false,
            ],
        ];

        $response2 = $this->actingAs($user)
            ->withSession(['cart' => $cartUnselected])
            ->get(route('checkout.index'));

        $response2->assertRedirect(route('cart.index'));
        $response2->assertSessionHas('warning');
    }

    public function test_order_creation_processes_only_selected_items_and_keeps_unselected_items_in_cart()
    {
        $user = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $product1 = Product::where('is_active', true)->first();
        $product2 = Product::where('is_active', true)->skip(1)->first() ?? $product1;

        $paymentMethod = PaymentMethod::where('is_active', true)->first();

        $cart = [
            'key_buy' => [
                'cart_key' => 'key_buy',
                'product_id' => $product1->id,
                'name' => 'Purchased Item',
                'price' => 150.00,
                'quantity' => 2,
                'selected' => true,
            ],
            'key_stay' => [
                'cart_key' => 'key_stay',
                'product_id' => $product2->id,
                'name' => 'Saved For Later Item',
                'price' => 300.00,
                'quantity' => 1,
                'selected' => false,
            ],
        ];

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post(route('checkout.store'), [
                'payment_method' => $paymentMethod ? $paymentMethod->name : 'Cash on Pickup',
                'fulfillment_type' => 'pickup',
            ]);

        $response->assertSessionHasNoErrors();

        $order = Order::where('user_id', $user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(300.00, (float) $order->total_amount); // 150 * 2
        $this->assertCount(1, $order->items);
        $this->assertEquals($product1->id, $order->items->first()->product_id);

        // Cart should still contain the unselected item 'key_stay'
        $remainingCart = session('cart');
        $this->assertNotEmpty($remainingCart);
        $this->assertArrayHasKey('key_stay', $remainingCart);
        $this->assertArrayNotHasKey('key_buy', $remainingCart);
    }
}
