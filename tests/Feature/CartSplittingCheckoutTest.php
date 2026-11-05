<?php

namespace Tests\Feature;

use App\Http\Controllers\Customer\CheckoutController;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CartSplittingCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Product $regularProduct;
    protected Product $customProduct;
    protected PaymentMethod $cashMethod;
    protected PaymentMethod $gcashMethod;
    protected PaymentMethod $mayaMethod;

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
            'name'                  => 'Ready-Made Acrylic Keychain',
            'slug'                  => 'acrylic-keychain',
            'category'              => 'Merchandise',
            'price'                 => 75.00,
            'base_price'            => 75.00,
            'requires_design_file'  => false,
            'requires_custom_terms' => false,
            'is_active'             => true,
        ]);

        $this->customProduct = Product::create([
            'name'                  => 'Personalized Heat Press Jersey',
            'slug'                  => 'personalized-jersey',
            'category'              => 'Apparel',
            'price'                 => 450.00,
            'base_price'            => 450.00,
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

        $this->mayaMethod = PaymentMethod::create([
            'name'              => 'Maya',
            'type'              => 'manual',
            'account_name'      => 'Grafika South',
            'account_number'    => '0917-000-0002',
            'is_online_payment' => true,
            'is_active'         => true,
        ]);
    }

    public function test_partition_cart_correctly_divides_mixed_items()
    {
        $cart = [
            'item_reg' => [
                'cart_key'             => 'item_reg',
                'product_id'           => $this->regularProduct->id,
                'name'                 => $this->regularProduct->name,
                'price'                => 75.00,
                'quantity'             => 2,
                'options'              => ['Color' => 'Clear'],
                'design_file_path'     => null,
                'requires_design_file' => false,
            ],
            'item_cust' => [
                'cart_key'             => 'item_cust',
                'product_id'           => $this->customProduct->id,
                'name'                 => $this->customProduct->name,
                'price'                => 450.00,
                'quantity'             => 1,
                'options'              => ['Size' => 'XL'],
                'design_file_path'     => null,
                'requires_design_file' => true,
            ]
        ];

        [$regularCart, $customCart] = CheckoutController::partitionCart($cart);

        $this->assertCount(1, $regularCart);
        $this->assertArrayHasKey('item_reg', $regularCart);
        $this->assertCount(1, $customCart);
        $this->assertArrayHasKey('item_cust', $customCart);
    }

    public function test_mixed_cart_checkout_page_renders_partitioned_sections_and_summary()
    {
        $cart = [
            'item_reg' => [
                'cart_key'             => 'item_reg',
                'product_id'           => $this->regularProduct->id,
                'name'                 => $this->regularProduct->name,
                'price'                => 75.00,
                'quantity'             => 2, // 150.00
                'options'              => [],
                'selected'             => true,
            ],
            'item_cust' => [
                'cart_key'             => 'item_cust',
                'product_id'           => $this->customProduct->id,
                'name'                 => $this->customProduct->name,
                'price'                => 450.00,
                'quantity'             => 2, // 900.00
                'options'              => [],
                'requires_design_file' => true,
                'selected'             => true,
            ]
        ];

        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->get(route('checkout.index'));

        $response->assertStatus(200);
        // Cart Splitting Active banner hidden by design (group cards suffice)
        $response->assertDontSee('Cart Splitting Active');
        $response->assertSee('Ready-Made / Regular Products');
        $response->assertSee('Customized / Personalized Items');
        $response->assertSee('150.00'); // Regular subtotal
        $response->assertSee('900.00'); // Custom subtotal
        $response->assertSee('regular_payment_method');
        $response->assertSee('custom_payment_method');
    }

    public function test_mixed_cart_placement_creates_two_separate_orders_with_independent_rules()
    {
        $cart = [
            'item_reg' => [
                'cart_key'             => 'item_reg',
                'product_id'           => $this->regularProduct->id,
                'name'                 => $this->regularProduct->name,
                'price'                => 75.00,
                'quantity'             => 2, // 150.00
                'selected'             => true,
            ],
            'item_cust' => [
                'cart_key'             => 'item_cust',
                'product_id'           => $this->customProduct->id,
                'name'                 => $this->customProduct->name,
                'price'                => 450.00,
                'quantity'             => 2, // 900.00
                'requires_design_file' => true,
                'selected'             => true,
            ]
        ];

        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->post(route('checkout.store'), [
                // Regular Group rules: Store pickup + Cash
                'regular_fulfillment_type' => 'pickup',
                'regular_payment_method'   => $this->cashMethod->name,

                // Custom Group rules: Courier delivery + GCash + 50% Downpayment
                'custom_fulfillment_type'  => 'delivery',
                'custom_payment_method'    => $this->gcashMethod->name,
                'custom_payment_plan'      => 'downpayment',
                'custom_terms_agreed'      => '1',

                'notes'                    => 'Please handle with care',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('customer.orders.index'));

        // Assert Parent Order and 2 linked sub-orders were created
        $orders = Order::where('user_id', $this->customer->id)->get();
        $this->assertCount(3, $orders);

        $parentOrder = $orders->firstWhere('is_parent', true);
        $this->assertNotNull($parentOrder);
        $this->assertEquals(1050.00, (float) $parentOrder->total_amount);
        $this->assertCount(2, $parentOrder->subOrders);

        $regularOrder = $parentOrder->subOrders->firstWhere('is_custom_order', false);
        $customOrder = $parentOrder->subOrders->firstWhere('is_custom_order', true);

        $this->assertEquals($parentOrder->id, $regularOrder->parent_id);
        $this->assertEquals($parentOrder->id, $customOrder->parent_id);

        // Verify Regular Order attributes
        $this->assertNotNull($regularOrder);
        $this->assertEquals(150.00, (float) $regularOrder->total_amount);
        $this->assertEquals('pickup', $regularOrder->fulfillment_type);
        $this->assertEquals('full', $regularOrder->payment_plan);
        $this->assertEquals(0.00, (float) $regularOrder->downpayment_amount);
        $this->assertCount(1, $regularOrder->items);
        $this->assertEquals($this->regularProduct->id, $regularOrder->items->first()->product_id);

        $regPayment = Payment::where('order_id', $regularOrder->id)->first();
        $this->assertNotNull($regPayment);
        $this->assertEquals($this->cashMethod->name, $regPayment->payment_method);
        $this->assertEquals(150.00, (float) $regPayment->amount);

        // Verify Custom Order attributes
        $this->assertNotNull($customOrder);
        $this->assertEquals(900.00, (float) $customOrder->total_amount);
        $this->assertEquals('delivery', $customOrder->fulfillment_type);
        $this->assertEquals('downpayment', $customOrder->payment_plan);
        $this->assertEquals(450.00, (float) $customOrder->downpayment_amount); // 50% of 900
        $this->assertEquals(450.00, (float) $customOrder->remaining_balance);
        $this->assertTrue((bool) $customOrder->custom_terms_agreed);
        $this->assertCount(1, $customOrder->items);
        $this->assertEquals($this->customProduct->id, $customOrder->items->first()->product_id);

        $custPayment = Payment::where('order_id', $customOrder->id)->first();
        $this->assertNotNull($custPayment);
        $this->assertEquals($this->gcashMethod->name, $custPayment->payment_method);
        $this->assertEquals(450.00, (float) $custPayment->amount);

        // Verify Cart was cleared
        $this->assertEmpty(session('cart', []));
    }

    public function test_custom_group_in_mixed_cart_rejects_cash_payment()
    {
        $cart = [
            'item_reg' => [
                'cart_key'             => 'item_reg',
                'product_id'           => $this->regularProduct->id,
                'name'                 => $this->regularProduct->name,
                'price'                => 75.00,
                'quantity'             => 1,
                'selected'             => true,
            ],
            'item_cust' => [
                'cart_key'             => 'item_cust',
                'product_id'           => $this->customProduct->id,
                'name'                 => $this->customProduct->name,
                'price'                => 450.00,
                'quantity'             => 1,
                'requires_design_file' => true,
                'selected'             => true,
            ]
        ];

        // Attempting to select Cash for the custom sub-order
        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->post(route('checkout.store'), [
                'regular_fulfillment_type' => 'pickup',
                'regular_payment_method'   => $this->cashMethod->name,

                'custom_fulfillment_type'  => 'pickup',
                'custom_payment_method'    => $this->cashMethod->name, // Invalid for custom!
                'custom_terms_agreed'      => '1',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_mixed_cart_renders_split_payment_summary_modal_and_notice(): void
    {
        $cart = [
            'item_reg' => [
                'cart_key'   => 'item_reg',
                'product_id' => $this->regularProduct->id,
                'name'       => $this->regularProduct->name,
                'price'      => 90.00,
                'quantity'   => 1,
                'selected'   => true,
            ],
            'item_cust' => [
                'cart_key'             => 'item_cust',
                'product_id'           => $this->customProduct->id,
                'name'                 => $this->customProduct->name,
                'price'                => 1812.00,
                'quantity'             => 2, // 3624.00 total, 1812.00 downpayment
                'requires_design_file' => true,
                'selected'             => true,
            ],
        ];

        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->get(route('checkout.index'));

        $response->assertStatus(200);

        // Assert the split breakdown modal and sidebar totals are present
        // (the redundant Split-Cart Payment Notice box was removed by design)
        $response->assertSee('Amount Due Today (Online Payment / Downpayment)');
        $response->assertSee('Amount Due Upon Pickup / Delivery (Cash)');
        $response->assertSee('Split Payment Summary Confirmation');
        $response->assertSee('splitPaymentModal');
        $response->assertSee('Total Due Now (Online)');
        $response->assertDontSee('Split-Cart Payment Notice');

        // Assert button is initially disabled with validation hint
        $response->assertSee('checkout_validation_hint');
        $response->assertSee('Please <strong>upload your payment screenshot</strong> to activate the order button', false);
    }

    public function test_custom_items_render_disabled_order_button_until_proof_uploaded(): void
    {
        $cart = [
            'item_cust' => [
                'cart_key'             => 'item_cust',
                'product_id'           => $this->customProduct->id,
                'name'                 => $this->customProduct->name,
                'price'                => 500.00,
                'quantity'             => 1,
                'requires_design_file' => true,
                'selected'             => true,
            ],
        ];

        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('id="submitOrderBtn"', false);
        $response->assertSee('disabled', false);
        $response->assertSee('checkout_validation_hint');
    }

    public function test_mixed_cart_payment_screenshot_persists_and_is_accessible_on_parent_and_sub_orders(): void
    {
        Storage::fake('public');
        $fakeScreenshot = UploadedFile::fake()->create('receipt.png', 150, 'image/png');

        $cart = [
            'item_reg' => [
                'cart_key'             => 'item_reg',
                'product_id'           => $this->regularProduct->id,
                'name'                 => $this->regularProduct->name,
                'price'                => 100.00,
                'quantity'             => 1,
                'selected'             => true,
            ],
            'item_cust' => [
                'cart_key'             => 'item_cust',
                'product_id'           => $this->customProduct->id,
                'name'                 => $this->customProduct->name,
                'price'                => 500.00,
                'quantity'             => 1,
                'requires_design_file' => true,
                'selected'             => true,
            ]
        ];

        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->post(route('checkout.store'), [
                'regular_fulfillment_type' => 'pickup',
                'regular_payment_method'   => $this->cashMethod->name,
                'custom_fulfillment_type'  => 'delivery',
                'custom_payment_method'    => $this->gcashMethod->name,
                'custom_payment_plan'      => 'downpayment',
                'custom_terms_agreed'      => '1',
                'custom_proof_of_payment'  => $fakeScreenshot,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('customer.orders.index'));

        $orders = Order::where('user_id', $this->customer->id)->get();
        $this->assertCount(3, $orders);

        $parentOrder = $orders->firstWhere('is_parent', true);
        $customOrder = $parentOrder->subOrders->firstWhere('is_custom_order', true);
        $regularOrder = $parentOrder->subOrders->firstWhere('is_custom_order', false);

        // Verify screenshot stored and accessible via payment_proof_path
        $this->assertNotNull($customOrder->payment);
        $this->assertNotNull($customOrder->payment->proof_of_payment_path);
        Storage::disk('public')->assertExists($customOrder->payment->proof_of_payment_path);

        // Verify parent order and child orders can access proof path accessor
        $this->assertEquals($customOrder->payment->proof_of_payment_path, $parentOrder->payment_proof_path);
        $this->assertEquals($customOrder->payment->proof_of_payment_path, $customOrder->payment_proof_path);

        // Verify payment status mapping
        $this->assertEquals('partially_paid', $customOrder->payment_status);
        $this->assertEquals('partially_paid', $parentOrder->payment_status);
        $this->assertEquals('unpaid', $regularOrder->payment_status);

        // Verify downpayment amounts
        $this->assertEquals(250.00, (float) $customOrder->downpayment_amount);
        $this->assertEquals(250.00, (float) $customOrder->remaining_balance);

        // Test customer order detail view for sub-order
        $subDetailResponse = $this->actingAs($this->customer)
            ->get(route('customer.orders.show', $customOrder->id));
        $subDetailResponse->assertStatus(200);
        $subDetailResponse->assertSee('Sub-Order Breakdown');
        $subDetailResponse->assertSee('Sub-Order Total:');
        $subDetailResponse->assertSee('Amount Paid (50% Downpayment):');
        $subDetailResponse->assertSee('Remaining Balance to be Paid on Delivery/Pickup:');
        $subDetailResponse->assertDontSee('No payment screenshot attached yet.');
        $subDetailResponse->assertSee('Screenshot Uploaded');

        // Test customer order detail view for parent order
        $parentDetailResponse = $this->actingAs($this->customer)
            ->get(route('customer.orders.show', $parentOrder->id));
        $parentDetailResponse->assertStatus(200);
        $parentDetailResponse->assertSee('Parent Order Breakdown');
        $parentDetailResponse->assertSee('Combined Parent Total:');
        $parentDetailResponse->assertDontSee('No payment screenshot attached yet.');
        $parentDetailResponse->assertSee('Screenshot Uploaded');

        // Test public track page
        $trackResponse = $this->get(route('orders.track', ['order_number' => $parentOrder->order_number]));
        $trackResponse->assertStatus(200);
        $trackResponse->assertSee('Amount Paid:');
        $trackResponse->assertSee('Remaining Balance to be Paid on Delivery/Pickup:');
        $trackResponse->assertSee('View Payment Screenshot');
    }
}
