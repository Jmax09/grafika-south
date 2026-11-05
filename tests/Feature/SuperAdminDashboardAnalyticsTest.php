<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $regularAdmin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email_verified_at' => now(),
        ]);

        $this->regularAdmin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);
    }

    public function test_super_admin_can_view_analytics_dashboard(): void
    {
        $product = Product::create([
            'name' => 'Custom Vinyl Banner',
            'slug' => 'custom-vinyl-banner',
            'category' => 'Marketing',
            'price' => 500.00,
            'base_price' => 500.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'GS-TEST-001',
            'user_id' => $this->customer->id,
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_plan' => 'full',
            'is_custom_order' => true,
            'total_price' => 1500.00,
            'total_amount' => 1500.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 500.00,
            'subtotal' => 1500.00,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'GCash',
            'amount' => 1500.00,
            'status' => 'verified',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('super-admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Business Analytics & Sales Dashboard', false);
        $response->assertSee('Revenue & Sales Trends', false);
        $response->assertSee('Custom vs Regular Orders', false);
        $response->assertSee('Top-Selling Products', false);
        $response->assertSee('Financial & Collection Overview', false);
        $response->assertSee('Custom Vinyl Banner');
    }

    public function test_super_admin_can_query_analytics_data_ajax(): void
    {
        $periods = ['daily', 'weekly', 'monthly', 'yearly'];

        foreach ($periods as $period) {
            $response = $this->actingAs($this->superAdmin)
                ->getJson(route('super-admin.analytics.data', ['period' => $period]));

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'labels',
                'revenue',
                'orders',
            ]);
        }
    }

    public function test_customer_is_redirected_away_from_super_admin_analytics(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('super-admin.dashboard'));

        $response->assertStatus(302);
        $response->assertRedirect(route('customer.dashboard'));
    }
}
