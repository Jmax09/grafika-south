<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiStatusTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Product $regularProduct;
    protected Product $customProduct;
    protected PaymentMethod $cashMethod;
    protected PaymentMethod $gcashMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name'              => 'Juana Dela Cruz',
            'email'             => 'juana@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'customer',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $this->regularProduct = Product::create([
            'name'                  => 'Plain White Mug',
            'slug'                  => 'plain-white-mug',
            'category'              => 'Mugs',
            'price'                 => 120.00,
            'base_price'            => 120.00,
            'requires_design_file'  => false,
            'requires_custom_terms' => false,
            'is_active'             => true,
        ]);

        $this->customProduct = Product::create([
            'name'                  => 'Personalized Heat Press Jersey',
            'slug'                  => 'personalized-jersey',
            'category'              => 'Apparel',
            'price'                 => 650.00,
            'base_price'            => 650.00,
            'requires_design_file'  => true,
            'requires_custom_terms' => true,
            'is_active'             => true,
        ]);

        $this->cashMethod = PaymentMethod::create([
            'name'              => 'Cash on Store Pickup',
            'type'              => 'cash',
            'is_online_payment' => false,
            'is_active'         => true,
        ]);

        $this->gcashMethod = PaymentMethod::create([
            'name'              => 'GCash',
            'type'              => 'manual',
            'account_name'      => 'Grafika South',
            'account_number'    => '0917-000-0001',
            'is_online_payment' => true,
            'is_active'         => true,
        ]);
    }

    public function test_cart_split_creates_parent_order_and_linked_sub_orders(): void
    {
        $cart = [
            'item_reg' => [
                'cart_key'             => 'item_reg',
                'product_id'           => $this->regularProduct->id,
                'name'                 => $this->regularProduct->name,
                'price'                => 120.00,
                'quantity'             => 2,
                'options'              => ['Color' => 'White'],
                'selected'             => true,
            ],
            'item_cust' => [
                'cart_key'             => 'item_cust',
                'product_id'           => $this->customProduct->id,
                'name'                 => $this->customProduct->name,
                'price'                => 650.00,
                'quantity'             => 1,
                'requires_design_file' => true,
                'selected'             => true,
            ],
        ];

        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->post(route('checkout.store'), [
                'regular_fulfillment_type' => 'pickup',
                'regular_payment_method'   => $this->cashMethod->name,
                'custom_fulfillment_type'  => 'delivery',
                'custom_payment_method'    => $this->gcashMethod->name,
                'custom_payment_plan'      => 'full',
                'custom_terms_agreed'      => '1',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('customer.orders.index'));

        // Verify total 3 orders in DB: 1 Parent + 2 Sub-Orders
        $allOrders = Order::where('user_id', $this->customer->id)->get();
        $this->assertCount(3, $allOrders);

        $parent = $allOrders->firstWhere('is_parent', true);
        $this->assertNotNull($parent);
        $this->assertNull($parent->parent_id);
        $this->assertTrue($parent->isParent());
        $this->assertFalse($parent->isSubOrder());
        $this->assertTrue($parent->hasSubOrders());
        $this->assertCount(2, $parent->subOrders);
        $this->assertEquals(890.00, (float) $parent->total_amount);

        // Sub-orders
        $regSub = $parent->subOrders->firstWhere('is_custom_order', false);
        $custSub = $parent->subOrders->firstWhere('is_custom_order', true);

        $this->assertNotNull($regSub);
        $this->assertNotNull($custSub);

        $this->assertEquals($parent->id, $regSub->parent_id);
        $this->assertEquals($parent->id, $custSub->parent_id);
        $this->assertTrue($regSub->isSubOrder());
        $this->assertTrue($custSub->isSubOrder());
        $this->assertFalse($regSub->isParent());
        $this->assertFalse($custSub->isParent());

        $this->assertEquals($parent->id, $regSub->parentOrder->id);
        $this->assertEquals($parent->id, $custSub->parentOrder->id);

        $this->assertEquals(240.00, (float) $regSub->total_amount);
        $this->assertEquals('pickup', $regSub->fulfillment_type);

        $this->assertEquals(650.00, (float) $custSub->total_amount);
        $this->assertEquals('delivery', $custSub->fulfillment_type);
    }

    public function test_independent_status_workflows_definitions(): void
    {
        $regularOrder = Order::create([
            'order_number'        => 'GS-20260903-REG-TEST1',
            'user_id'             => $this->customer->id,
            'status'              => 'pending',
            'is_custom_order'     => false,
            'fulfillment_type'    => 'pickup',
            'total_amount'        => 240.00,
        ]);

        $customOrder = Order::create([
            'order_number'        => 'GS-20260903-CUST-TEST1',
            'user_id'             => $this->customer->id,
            'status'              => 'pending',
            'is_custom_order'     => true,
            'fulfillment_type'    => 'delivery',
            'total_amount'        => 650.00,
        ]);

        // Regular workflow: 3 stages
        $regStages = $regularOrder->workflow_stages;
        $this->assertCount(3, $regStages);
        $this->assertArrayHasKey('pending', $regStages);
        $this->assertArrayHasKey('ready', $regStages);
        $this->assertArrayHasKey('completed', $regStages);
        $this->assertEquals(1, $regularOrder->current_workflow_step);

        $regularOrder->status = 'ready';
        $this->assertEquals(2, $regularOrder->current_workflow_step);

        $regularOrder->status = 'completed';
        $this->assertEquals(3, $regularOrder->current_workflow_step);

        // Custom workflow: 5 stages
        $custStages = $customOrder->workflow_stages;
        $this->assertCount(5, $custStages);
        $this->assertArrayHasKey('pending', $custStages);
        $this->assertArrayHasKey('design_review', $custStages);
        $this->assertArrayHasKey('production', $custStages);
        $this->assertArrayHasKey('ready', $custStages);
        $this->assertArrayHasKey('completed', $custStages);
        $this->assertEquals(1, $customOrder->current_workflow_step);

        $customOrder->status = 'design_review';
        $this->assertEquals(2, $customOrder->current_workflow_step);

        $customOrder->status = 'printing';
        $this->assertEquals(3, $customOrder->current_workflow_step);

        $customOrder->status = 'ready';
        $this->assertEquals(4, $customOrder->current_workflow_step);

        $customOrder->status = 'completed';
        $this->assertEquals(5, $customOrder->current_workflow_step);
    }

    public function test_track_trace_with_parent_order_shows_both_sub_orders(): void
    {
        $parent = Order::create([
            'order_number'     => 'GS-20260903-PARENT99',
            'user_id'          => $this->customer->id,
            'is_parent'        => true,
            'status'           => 'pending',
            'total_amount'     => 890.00,
            'fulfillment_type' => 'pickup',
        ]);

        $regSub = Order::create([
            'order_number'     => 'GS-20260903-REG-99A',
            'parent_id'        => $parent->id,
            'user_id'          => $this->customer->id,
            'status'           => 'ready',
            'is_custom_order'  => false,
            'fulfillment_type' => 'pickup',
            'total_amount'     => 240.00,
        ]);

        OrderItem::create([
            'order_id'   => $regSub->id,
            'product_id' => $this->regularProduct->id,
            'quantity'   => 2,
            'unit_price' => 120.00,
            'subtotal'   => 240.00,
        ]);

        $custSub = Order::create([
            'order_number'     => 'GS-20260903-CUST-99B',
            'parent_id'        => $parent->id,
            'user_id'          => $this->customer->id,
            'status'           => 'design_review',
            'is_custom_order'  => true,
            'fulfillment_type' => 'delivery',
            'total_amount'     => 650.00,
        ]);

        OrderItem::create([
            'order_id'   => $custSub->id,
            'product_id' => $this->customProduct->id,
            'quantity'   => 1,
            'unit_price' => 650.00,
            'subtotal'   => 650.00,
        ]);

        // Track using parent order number
        $response = $this->get(route('orders.track', ['order_number' => $parent->order_number]));

        $response->assertStatus(200);
        $response->assertSee('GS-20260903-PARENT99');
        $response->assertSee('GS-20260903-REG-99A');
        $response->assertSee('GS-20260903-CUST-99B');
        $response->assertSee('Multi-Status Tracking Active');
        $response->assertSee('Ready-Made / Regular Sub-Order');
        $response->assertSee('Customized Print Sub-Order');
        $response->assertSee('Store Pickup / Delivery Workflow');
        $response->assertSee('Custom Production Workflow');
    }

    public function test_track_trace_with_sub_order_shows_sub_order_workflow_and_parent_link(): void
    {
        $parent = Order::create([
            'order_number'     => 'GS-20260903-PARENT77',
            'user_id'          => $this->customer->id,
            'is_parent'        => true,
            'status'           => 'pending',
            'total_amount'     => 890.00,
            'fulfillment_type' => 'pickup',
        ]);

        $regSub = Order::create([
            'order_number'     => 'GS-20260903-REG-77A',
            'parent_id'        => $parent->id,
            'user_id'          => $this->customer->id,
            'status'           => 'pending',
            'is_custom_order'  => false,
            'fulfillment_type' => 'pickup',
            'total_amount'     => 240.00,
        ]);

        OrderItem::create([
            'order_id'   => $regSub->id,
            'product_id' => $this->regularProduct->id,
            'quantity'   => 2,
            'unit_price' => 120.00,
            'subtotal'   => 240.00,
        ]);

        // Track using child sub-order number
        $response = $this->get(route('orders.track', ['order_number' => $regSub->order_number]));

        $response->assertStatus(200);
        $response->assertSee('GS-20260903-REG-77A');
        $response->assertSee('Part of Split Order #GS-20260903-PARENT77');
        $response->assertSee('Track Entire Split Order');
    }

    public function test_customer_order_detail_renders_multi_sub_order_hub_for_parent_order(): void
    {
        $parent = Order::create([
            'order_number'     => 'GS-20260903-PARENT55',
            'user_id'          => $this->customer->id,
            'is_parent'        => true,
            'status'           => 'pending',
            'total_amount'     => 890.00,
            'fulfillment_type' => 'pickup',
        ]);

        $regSub = Order::create([
            'order_number'     => 'GS-20260903-REG-55A',
            'parent_id'        => $parent->id,
            'user_id'          => $this->customer->id,
            'status'           => 'pending',
            'is_custom_order'  => false,
            'fulfillment_type' => 'pickup',
            'total_amount'     => 240.00,
        ]);

        $custSub = Order::create([
            'order_number'     => 'GS-20260903-CUST-55B',
            'parent_id'        => $parent->id,
            'user_id'          => $this->customer->id,
            'status'           => 'pending',
            'is_custom_order'  => true,
            'fulfillment_type' => 'delivery',
            'total_amount'     => 650.00,
        ]);

        $response = $this->actingAs($this->customer)->get(route('customer.orders.show', $parent->id));

        $response->assertStatus(200);
        $response->assertSee('Split Order Tracking Hub');
        $response->assertSee('Multi-Status Tracking Active');
        $response->assertSee('GS-20260903-REG-55A');
        $response->assertSee('GS-20260903-CUST-55B');
    }
}
