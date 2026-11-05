<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerOrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected User $superAdmin;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name'              => 'Pedro Penduko',
            'email'             => 'pedro@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'customer',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $this->admin = User::create([
            'name'              => 'Admin Staff',
            'email'             => 'admin@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'admin',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $this->superAdmin = User::create([
            'name'              => 'Super Admin',
            'email'             => 'superadmin@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'super_admin',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $this->product = Product::create([
            'name'                  => 'Commercial Tarpaulin Banner',
            'slug'                  => 'commercial-tarpaulin-banner',
            'category'              => 'Banners',
            'price'                 => 1200.00,
            'base_price'            => 1200.00,
            'requires_design_file'  => true,
            'requires_custom_terms' => false,
            'is_active'             => true,
        ]);
    }

    private function createTestOrder(array $attributes = []): Order
    {
        $order = Order::create(array_merge([
            'order_number'    => 'GS-TEST-' . uniqid(),
            'user_id'         => $this->customer->id,
            'status'          => 'pending',
            'payment_status'  => 'unpaid',
            'payment_plan'    => 'full',
            'total_price'     => 1200.00,
            'total_amount'    => 1200.00,
        ], $attributes));

        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $this->product->id,
            'quantity'   => 1,
            'unit_price' => 1200.00,
            'subtotal'   => 1200.00,
        ]);

        return $order;
    }

    #[Test]
    public function customer_can_see_cancel_button_on_early_stage_orders(): void
    {
        $pendingOrder = $this->createTestOrder(['status' => 'pending']);
        $reviewOrder  = $this->createTestOrder(['status' => 'design_review']);

        $res1 = $this->actingAs($this->customer)->get(route('customer.orders.show', $pendingOrder->id));
        $res1->assertOk();
        $res1->assertSee('Cancel Order');
        $res1->assertSee('openCancelOrderModal()', false);

        $res2 = $this->actingAs($this->customer)->get(route('customer.orders.show', $reviewOrder->id));
        $res2->assertOk();
        $res2->assertSee('Cancel Order');
    }

    #[Test]
    public function customer_cannot_see_cancel_button_on_approved_or_in_production_orders(): void
    {
        $approvedOrder = $this->createTestOrder(['status' => 'approved']);
        $printingOrder = $this->createTestOrder(['status' => 'printing']);
        $completedOrder = $this->createTestOrder(['status' => 'completed']);

        $res1 = $this->actingAs($this->customer)->get(route('customer.orders.show', $approvedOrder->id));
        $res1->assertOk();
        $res1->assertDontSee('Cancel Order');

        $res2 = $this->actingAs($this->customer)->get(route('customer.orders.show', $printingOrder->id));
        $res2->assertOk();
        $res2->assertDontSee('Cancel Order');

        $res3 = $this->actingAs($this->customer)->get(route('customer.orders.show', $completedOrder->id));
        $res3->assertOk();
        $res3->assertDontSee('Cancel Order');
    }

    #[Test]
    public function customer_can_cancel_pending_order_with_valid_reason(): void
    {
        $order = $this->createTestOrder(['status' => 'pending', 'payment_status' => 'unpaid']);

        $response = $this->actingAs($this->customer)->post(route('customer.orders.cancel', $order->id), [
            'cancellation_reason' => 'Changed mind regarding the sizing.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('approved', $order->cancellation_status);
        $this->assertEquals('Changed mind regarding the sizing.', $order->cancellation_reason);
        $this->assertNotNull($order->cancelled_at);
        $this->assertEquals('not_applicable', $order->refund_status);
    }

    #[Test]
    public function cancellation_of_paid_order_triggers_refund_in_progress(): void
    {
        $order = $this->createTestOrder([
            'status'         => 'design_review',
            'payment_status' => 'paid',
            'total_price'    => 1200.00,
            'total_amount'   => 1200.00,
        ]);

        Payment::create([
            'order_id'       => $order->id,
            'amount'         => 1200.00,
            'payment_method' => 'GCash',
            'status'         => 'verified',
        ]);

        $response = $this->actingAs($this->customer)->post(route('customer.orders.cancel', $order->id), [
            'cancellation_reason' => 'Duplicate order placed by mistake.',
            'refund_method'       => 'GCash - 09171234567',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('in_progress', $order->refund_status);
        $this->assertEquals('GCash - 09171234567', $order->refund_method);
        $this->assertEquals(1200.00, (float) $order->refund_amount);
    }

    #[Test]
    public function customer_cannot_cancel_order_after_approval(): void
    {
        $order = $this->createTestOrder(['status' => 'approved']);

        $response = $this->actingAs($this->customer)->post(route('customer.orders.cancel', $order->id), [
            'cancellation_reason' => 'Late cancellation attempt.',
        ]);

        $response->assertSessionHas('error');
        $order->refresh();
        $this->assertEquals('approved', $order->status);
    }

    #[Test]
    public function order_details_view_renders_cancellation_and_refund_tracker(): void
    {
        $order = $this->createTestOrder([
            'status'              => 'cancelled',
            'cancellation_status' => 'approved',
            'cancellation_reason' => 'Wrong banner dimensions entered.',
            'refund_status'       => 'in_progress',
            'refund_method'       => 'GCash',
            'refund_amount'       => 1200.00,
            'cancelled_at'        => now(),
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.orders.show', $order->id));

        $response->assertOk();
        $response->assertSee('Cancellation &amp; Refund Progress Tracker', false);
        $response->assertSee('Refund in Progress');
        $response->assertSee('Reason for Cancellation:');
        $response->assertSee('Wrong banner dimensions entered.');
        $response->assertSee('Cancelled Items Breakdown');
        $response->assertSee('CANCELLED');
    }

    #[Test]
    public function super_admin_can_process_refund_and_mark_completed(): void
    {
        $order = $this->createTestOrder([
            'status'         => 'cancelled',
            'payment_status' => 'paid',
            'refund_status'  => 'in_progress',
            'refund_amount'  => 1200.00,
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('super-admin.orders.refund', $order->id), [
            'action'           => 'refund',
            'refund_method'    => 'GCash',
            'refund_reference' => 'GCASH-REF-998877',
            'refund_amount'    => 1200.00,
            'notes'            => 'Manual GCash payout sent to customer.',
        ]);

        $response->assertRedirect();
        $order->refresh();

        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('refunded', $order->payment_status);
        $this->assertEquals('refunded', $order->refund_status);
        $this->assertEquals('GCash', $order->refund_method);
        $this->assertEquals('GCASH-REF-998877', $order->refund_reference);
        $this->assertNotNull($order->refunded_at);
    }
}