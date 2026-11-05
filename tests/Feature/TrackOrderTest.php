<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Models\Material::create([
            'name' => 'Paper Stock',
            'unit' => 'sheets',
            'current_stock' => 1000,
            'minimum_stock' => 10,
        ]);

        Product::create([
            'name' => 'Custom T-Shirt',
            'slug' => 'custom-t-shirt',
            'category' => 'Apparel',
            'price' => 150.00,
            'base_price' => 150.00,
            'is_active' => true,
        ]);
    }

    public function test_can_access_track_order_page()
    {
        $response = $this->get('/track-order');

        $response->assertStatus(200);
        $response->assertSee('Track & Trace Order Status');
    }

    public function test_can_search_order_by_tracking_number()
    {
        $product = Product::first();

        $order = Order::create([
            'order_number' => 'GS-20260817-6CB93',
            'user_id' => null,
            'is_walkin' => true,
            'walkin_first_name' => 'Juan',
            'walkin_last_name' => 'dela Cruz',
            'status' => 'in_production',
            'payment_status' => 'paid',
            'total_price' => 150.00,
            'total_amount' => 150.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 150.00,
            'subtotal' => 150.00,
        ]);

        // Search with hash symbol
        $response = $this->get('/track-order?order_number=%23GS-20260817-6CB93');

        $response->assertStatus(200);
        $response->assertSee('GS-20260817-6CB93');
        $response->assertSee('Juan dela Cruz');
        $response->assertSee('PRINTING');
    }
}
