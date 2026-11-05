<?php

namespace Tests\Feature;

use App\Http\Controllers\Customer\CheckoutController;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegularVsCustomCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Product $regularProduct;
    protected Product $customProduct;
    protected PaymentMethod $cashMethod;
    protected PaymentMethod $digitalMethod;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name'              => 'Juan Dela Cruz',
            'email'             => 'juan@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'customer',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $this->regularProduct = Product::create([
            'name'                  => 'Ready-Made Standard Notebook',
            'slug'                  => 'standard-notebook',
            'category'              => 'Stationery',
            'price'                 => 120.00,
            'base_price'            => 120.00,
            'requires_design_file'  => false,
            'requires_custom_terms' => false,
            'is_active'             => true,
        ]);

        $this->customProduct = Product::create([
            'name'                  => 'Customized Acrylic Plaque',
            'slug'                  => 'custom-acrylic-plaque',
            'category'              => 'Awards',
            'price'                 => 850.00,
            'base_price'            => 850.00,
            'requires_design_file'  => true,
            'requires_custom_terms' => true,
            'is_active'             => true,
        ]);

        $this->cashMethod = PaymentMethod::create([
            'name'              => 'Cash on Pickup / Store',
            'type'              => 'cash',
            'is_online_payment' => false,
            'is_active'         => true,
        ]);

        $this->digitalMethod = PaymentMethod::create([
            'name'              => 'GCash',
            'type'              => 'manual',
            'is_online_payment' => true,
            'is_active'         => true,
        ]);
    }

    public function test_regular_product_with_options_and_notes_is_detected_as_regular()
    {
        $cart = [
            'item_1' => [
                'cart_key'             => 'item_1',
                'product_id'           => $this->regularProduct->id,
                'name'                 => $this->regularProduct->name,
                'price'                => 120.00,
                'quantity'             => 2,
                'options'              => ['Color' => 'Navy Blue', 'Ruling' => 'Grid Line'],
                'design_file_path'     => null,
                'requires_design_file' => false,
                'special_instructions' => 'Please bubble wrap safely for transit.',
                'selected'             => true,
            ]
        ];

        $this->assertFalse(CheckoutController::isCustomCart($cart));
        $this->assertFalse(CheckoutController::cartRequiresCustomTerms($cart));
    }

    public function test_regular_cart_allows_store_pickup_and_cash_payment_channels()
    {
        $cart = [
            'item_1' => [
                'cart_key'             => 'item_1',
                'product_id'           => $this->regularProduct->id,
                'name'                 => $this->regularProduct->name,
                'price'                => 120.00,
                'quantity'             => 1,
                'options'              => ['Size' => 'A5'],
                'design_file_path'     => null,
                'requires_design_file' => false,
                'selected'             => true,
            ]
        ];

        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('Cash on Pickup / Store');
        $response->assertSee('GCash');
        $response->assertDontSee('Restricted (Custom Order)');
        $response->assertDontSee('Custom Order Payment Policy');
    }

    public function test_regular_order_can_be_placed_with_cash_on_pickup_without_proof_file()
    {
        $cart = [
            'item_1' => [
                'cart_key'             => 'item_1',
                'product_id'           => $this->regularProduct->id,
                'name'                 => $this->regularProduct->name,
                'price'                => 120.00,
                'quantity'             => 3,
                'options'              => ['Color' => 'Red'],
                'design_file_path'     => null,
                'requires_design_file' => false,
                'selected'             => true,
            ]
        ];

        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->post(route('checkout.store'), [
                'fulfillment_type' => 'pickup',
                'payment_method'   => $this->cashMethod->name,
                'payment_plan'     => 'full',
            ]);

        $response->assertSessionHasNoErrors();
        $order = Order::where('user_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(360.00, (float) $order->total_amount);
        $this->assertEquals('pickup', $order->fulfillment_type);
        $this->assertFalse((bool) $order->is_custom_order);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals($this->cashMethod->name, $payment->payment_method);
        $this->assertEquals(360.00, (float) $payment->amount);
    }

    public function test_custom_product_activates_custom_rules_and_rejects_cash()
    {
        $cart = [
            'item_custom' => [
                'cart_key'             => 'item_custom',
                'product_id'           => $this->customProduct->id,
                'name'                 => $this->customProduct->name,
                'price'                => 850.00,
                'quantity'             => 1,
                'options'              => [],
                'design_file_path'     => null,
                'requires_design_file' => true,
                'selected'             => true,
            ]
        ];

        $this->assertTrue(CheckoutController::isCustomCart($cart));
        $this->assertTrue(CheckoutController::cartRequiresCustomTerms($cart));

        // Checkout page hides cash
        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertDontSee($this->cashMethod->name);
        $response->assertSee($this->digitalMethod->name);
        
        // Attempting to submit cash for custom item fails
        $submitResponse = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->post(route('checkout.store'), [
                'fulfillment_type'    => 'pickup',
                'payment_method'      => $this->cashMethod->name,
                'payment_plan'        => 'full',
                'custom_terms_agreed' => '1',
            ]);

        $submitResponse->assertSessionHas('error');
    }

    public function test_mixed_cart_with_one_custom_item_applies_custom_protection_rules()
    {
        $cart = [
            'item_reg' => [
                'cart_key'             => 'item_reg',
                'product_id'           => $this->regularProduct->id,
                'name'                 => $this->regularProduct->name,
                'price'                => 120.00,
                'quantity'             => 1,
                'selected'             => true,
            ],
            'item_custom' => [
                'cart_key'             => 'item_custom',
                'product_id'           => $this->customProduct->id,
                'name'                 => $this->customProduct->name,
                'price'                => 850.00,
                'quantity'             => 1,
                'selected'             => true,
            ],
        ];

        $this->assertTrue(CheckoutController::isCustomCart($cart));

        $response = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->get(route('checkout.index'));

        $response->assertStatus(200);
        // Cart Splitting Active banner hidden by design; the split submit button
        // is the remaining mixed-cart page marker.
        $response->assertSee('Place Order (2 Sub-Orders)');
        $response->assertSee('Ready-Made / Regular Products');
        $response->assertSee('Customized / Personalized Items');
        $response->assertSee($this->cashMethod->name); // Available for regular group
        $response->assertSee($this->digitalMethod->name); // Available for custom group

        // Submitting cash for custom group fails
        $submitResponse = $this->actingAs($this->customer)
            ->withSession(['cart' => $cart])
            ->post(route('checkout.store'), [
                'regular_fulfillment_type' => 'pickup',
                'regular_payment_method'   => $this->cashMethod->name,
                'custom_fulfillment_type'  => 'delivery',
                'custom_payment_method'    => $this->cashMethod->name, // Invalid for custom!
                'custom_terms_agreed'      => '1',
            ]);

        $submitResponse->assertSessionHas('error');
    }
}
