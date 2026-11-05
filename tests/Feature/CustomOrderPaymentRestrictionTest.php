<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomOrderPaymentRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Product $standardProduct;
    protected Product $customProduct;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic payment methods
        PaymentMethod::create([
            'name' => 'GCash',
            'type' => 'manual',
            'account_name' => 'Grafika South',
            'account_number' => '0917-000-0001',
            'is_online_payment' => true,
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Maya / PayMaya',
            'type' => 'manual',
            'account_name' => 'Grafika South',
            'account_number' => '0917-000-0002',
            'is_online_payment' => true,
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Bank Transfer (BDO / BPI)',
            'type' => 'manual',
            'account_name' => 'Grafika South',
            'account_number' => 'BDO: 123-456',
            'is_online_payment' => true,
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Cash on Pickup / Store',
            'type' => 'manual',
            'account_name' => 'San Pedro Branch',
            'account_number' => 'Pay upon pickup',
            'is_online_payment' => false,
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->standardProduct = Product::create([
            'name' => 'Standard Plain Mug',
            'slug' => 'standard-plain-mug',
            'category' => 'Mugs',
            'price' => 200.00,
            'base_price' => 200.00,
            'requires_design_file' => false,
            'is_active' => true,
        ]);

        $this->customProduct = Product::create([
            'name' => 'Customized Vinyl Banner',
            'slug' => 'customized-vinyl-banner',
            'category' => 'Banners',
            'price' => 1000.00,
            'base_price' => 1000.00,
            'requires_design_file' => true,
            'is_active' => true,
        ]);
    }

    public function test_standard_cart_allows_all_payment_methods_including_cash_on_pickup()
    {
        $this->actingAs($this->customer);

        // Put standard item in cart
        session()->put('cart', [
            'prod_' . $this->standardProduct->id => [
                'cart_key' => 'prod_' . $this->standardProduct->id,
                'product_id' => $this->standardProduct->id,
                'name' => $this->standardProduct->name,
                'price' => 200.00,
                'quantity' => 1,
                'options' => [],
                'design_file_path' => null,
                'special_instructions' => '',
            ]
        ]);

        $response = $this->get('/checkout');
        $response->assertStatus(200);
        $response->assertSee('Cash on Pickup / Store');
        $response->assertSee('GCash');
        $response->assertDontSee('Custom Order Payment Policy');
    }

    public function test_custom_cart_restricts_payment_methods_and_hides_cash_on_pickup()
    {
        $this->actingAs($this->customer);

        // Put custom item in cart (product requires design file)
        session()->put('cart', [
            'prod_' . $this->customProduct->id => [
                'cart_key' => 'prod_' . $this->customProduct->id,
                'product_id' => $this->customProduct->id,
                'name' => $this->customProduct->name,
                'price' => 1000.00,
                'quantity' => 1,
                'options' => ['Size' => '3x5 ft'],
                'design_file_path' => 'order_files_temp/sample_design.pdf',
                'special_instructions' => 'Matte finish please',
            ]
        ]);

        $response = $this->get('/checkout');
        $response->assertStatus(200);
        $response->assertSee('Custom Order Payment Policy');
        $response->assertSee('Payment Term: Downpayment or Full Payment');
        $response->assertSee('50% Downpayment');
        $response->assertSee('100% Full Payment');
        $response->assertSee('GCash');
        $response->assertSee('Maya');
        $response->assertSee('Bank Transfer');
        // Cash on pickup should be filtered out
        $response->assertDontSee('Cash on Pickup / Store');
    }

    public function test_backend_rejects_cash_payment_for_customized_order()
    {
        $this->actingAs($this->customer);

        session()->put('cart', [
            'prod_' . $this->customProduct->id => [
                'cart_key' => 'prod_' . $this->customProduct->id,
                'product_id' => $this->customProduct->id,
                'name' => $this->customProduct->name,
                'price' => 1000.00,
                'quantity' => 1,
                'options' => [],
                'design_file_path' => null,
                'special_instructions' => '',
            ]
        ]);

        // Attempt to submit order with Cash on Pickup / COD
        $response = $this->post('/checkout', [
            'payment_method' => 'Cash on Pickup / Store',
            'payment_plan' => 'full',
            'fulfillment_type' => 'pickup',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_custom_order_with_50_percent_downpayment_creates_order_and_payment_correctly()
    {
        $this->actingAs($this->customer);

        session()->put('cart', [
            'prod_' . $this->customProduct->id => [
                'cart_key' => 'prod_' . $this->customProduct->id,
                'product_id' => $this->customProduct->id,
                'name' => $this->customProduct->name,
                'price' => 1000.00,
                'quantity' => 2, // Total = 2000.00
                'options' => ['Material' => 'Tarpaulin'],
                'design_file_path' => null,
                'special_instructions' => '',
            ]
        ]);

        $response = $this->post('/checkout', [
            'payment_method' => 'GCash',
            'payment_plan' => 'downpayment',
            'fulfillment_type' => 'pickup',
            'notes' => 'Test downpayment order',
        ]);

        $order = Order::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('customer.orders.show', $order->id));

        // Check Order amounts
        $this->assertEquals(2000.00, (float) $order->total_amount);
        $this->assertEquals('downpayment', $order->payment_plan);
        $this->assertEquals(1000.00, (float) $order->downpayment_amount);
        $this->assertEquals(1000.00, (float) $order->remaining_balance);
        $this->assertTrue((bool) $order->is_custom_order);

        // Check Payment transaction record
        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('GCash', $payment->payment_method);
        $this->assertEquals(1000.00, (float) $payment->amount);
        $this->assertEquals('downpayment', $payment->payment_plan);
    }

    public function test_custom_order_with_full_payment_creates_order_with_zero_remaining_balance()
    {
        $this->actingAs($this->customer);

        session()->put('cart', [
            'prod_' . $this->customProduct->id => [
                'cart_key' => 'prod_' . $this->customProduct->id,
                'product_id' => $this->customProduct->id,
                'name' => $this->customProduct->name,
                'price' => 1500.00,
                'quantity' => 1,
                'options' => [],
                'design_file_path' => null,
                'special_instructions' => '',
            ]
        ]);

        $response = $this->post('/checkout', [
            'payment_method' => 'Maya / PayMaya',
            'payment_plan' => 'full',
            'fulfillment_type' => 'pickup',
        ]);

        $order = Order::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('customer.orders.show', $order->id));

        $this->assertEquals(1500.00, (float) $order->total_amount);
        $this->assertEquals('full', $order->payment_plan);
        $this->assertEquals(0.00, (float) $order->downpayment_amount);
        $this->assertEquals(0.00, (float) $order->remaining_balance);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(1500.00, (float) $payment->amount);
        $this->assertEquals('full', $payment->payment_plan);
    }

    public function test_order_detail_and_invoice_pages_render_downpayment_and_remaining_balance()
    {
        $this->actingAs($this->customer);

        $order = Order::create([
            'order_number' => 'GS-20260831-TEST1',
            'user_id' => $this->customer->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_plan' => 'downpayment',
            'downpayment_amount' => 500.00,
            'remaining_balance' => 500.00,
            'is_custom_order' => true,
            'total_price' => 1000.00,
            'total_amount' => 1000.00,
            'fulfillment_type' => 'pickup',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'GCash',
            'amount' => 500.00,
            'payment_plan' => 'downpayment',
            'status' => 'pending',
        ]);

        // Customer Order detail page
        $detailResponse = $this->get(route('customer.orders.show', $order->id));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('50% Downpayment');
        $detailResponse->assertSee('Remaining Balance Due:');
        $detailResponse->assertSee('500.00');

        // Invoice page
        $invoiceResponse = $this->get(route('customer.orders.invoice', $order->id));
        $invoiceResponse->assertStatus(200);
        $invoiceResponse->assertSee('50% Downpayment');
        $invoiceResponse->assertSee('Remaining Balance');
    }
}
