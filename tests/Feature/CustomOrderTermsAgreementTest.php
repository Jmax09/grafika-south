<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomOrderTermsAgreementTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected PaymentMethod $gcash;
    protected PaymentMethod $cashOnPickup;
    protected Product $customTermsProduct;
    protected Product $standardProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'name'              => 'Test Customer',
            'email'             => 'customer@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'customer',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $this->gcash = PaymentMethod::create([
            'name'              => 'GCash',
            'type'              => 'manual',
            'account_name'      => 'Grafika South',
            'account_number'    => '0917-000-0001',
            'is_online_payment' => true,
            'is_active'         => true,
        ]);

        $this->cashOnPickup = PaymentMethod::create([
            'name'              => 'Cash on Pickup',
            'type'              => 'manual',
            'account_name'      => 'San Pedro Branch',
            'account_number'    => 'Pay upon pickup',
            'is_online_payment' => false,
            'is_active'         => true,
        ]);

        $this->customTermsProduct = Product::create([
            'name'                  => 'Custom Tarpaulin Print',
            'slug'                  => 'custom-tarpaulin-print',
            'category'              => 'Banners',
            'price'                 => 500.00,
            'base_price'            => 500.00,
            'requires_design_file'  => false,
            'requires_custom_terms' => true,
            'is_active'             => true,
        ]);

        $this->standardProduct = Product::create([
            'name'                  => 'Standard Mug',
            'slug'                  => 'standard-mug',
            'category'              => 'Mugs',
            'price'                 => 300.00,
            'base_price'            => 300.00,
            'requires_design_file'  => false,
            'requires_custom_terms' => false,
            'is_active'             => true,
        ]);
    }

    private function cartWith(Product $product, int $qty = 1): array
    {
        return [
            'item_' . $product->id => [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => (float) $product->price,
                'quantity'   => $qty,
                'options'    => [],
            ],
        ];
    }

    // ─── 1. Product model ────────────────────────────────────────────────────

    /** @test */
    public function product_requires_custom_terms_defaults_to_false(): void
    {
        $this->assertFalse((bool) $this->standardProduct->requires_custom_terms);
    }

    /** @test */
    public function product_can_store_requires_custom_terms_true(): void
    {
        $this->assertTrue((bool) $this->customTermsProduct->fresh()->requires_custom_terms);
    }

    // ─── 2. cartRequiresCustomTerms helper ──────────────────────────────────

    /** @test */
    public function cart_requires_custom_terms_returns_true_when_product_has_flag(): void
    {
        $cart = $this->cartWith($this->customTermsProduct);
        $this->assertTrue(
            \App\Http\Controllers\Customer\CheckoutController::cartRequiresCustomTerms($cart)
        );
    }

    /** @test */
    public function cart_requires_custom_terms_returns_false_for_standard_product(): void
    {
        $cart = $this->cartWith($this->standardProduct);
        $this->assertFalse(
            \App\Http\Controllers\Customer\CheckoutController::cartRequiresCustomTerms($cart)
        );
    }

    /** @test */
    public function cart_requires_custom_terms_returns_false_for_empty_cart(): void
    {
        $this->assertFalse(
            \App\Http\Controllers\Customer\CheckoutController::cartRequiresCustomTerms([])
        );
    }

    // ─── 3. Checkout page passes correct variable ────────────────────────────

    /** @test */
    public function checkout_page_sets_requires_custom_terms_true_for_custom_cart(): void
    {
        $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customTermsProduct)])
             ->get(route('checkout.index'))
             ->assertOk()
             ->assertViewHas('requiresCustomTerms', true);
    }

    /** @test */
    public function checkout_page_sets_requires_custom_terms_false_for_standard_cart(): void
    {
        $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->standardProduct)])
             ->get(route('checkout.index'))
             ->assertOk()
             ->assertViewHas('requiresCustomTerms', false);
    }

    // ─── 4. Checkout validation ──────────────────────────────────────────────

    /** @test */
    public function checkout_fails_when_custom_terms_checkbox_is_missing(): void
    {
        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customTermsProduct)])
             ->post(route('checkout.store'), [
                 'payment_method'   => $this->gcash->name,
                 'payment_plan'     => 'full',
                 'fulfillment_type' => 'delivery',
                 // custom_terms_agreed intentionally omitted
             ]);

        $response->assertSessionHasErrors('custom_terms_agreed');
    }

    /** @test */
    public function checkout_fails_when_custom_terms_checkbox_is_unchecked_zero(): void
    {
        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customTermsProduct)])
             ->post(route('checkout.store'), [
                 'payment_method'      => $this->gcash->name,
                 'payment_plan'        => 'full',
                 'fulfillment_type'    => 'delivery',
                 'custom_terms_agreed' => '0',
             ]);

        $response->assertSessionHasErrors('custom_terms_agreed');
    }

    /** @test */
    public function checkout_succeeds_when_custom_terms_checkbox_is_checked(): void
    {
        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customTermsProduct)])
             ->post(route('checkout.store'), [
                 'payment_method'      => $this->gcash->name,
                 'payment_plan'        => 'full',
                 'fulfillment_type'    => 'delivery',
                 'custom_terms_agreed' => '1',
             ]);

        $response->assertSessionHasNoErrors();

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertTrue((bool) $order->custom_terms_agreed);
    }

    // ─── 5. Standard product does NOT require terms checkbox ─────────────────

    /** @test */
    public function checkout_succeeds_for_standard_product_without_terms_checkbox(): void
    {
        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->standardProduct)])
             ->post(route('checkout.store'), [
                 'payment_method'   => $this->cashOnPickup->name,
                 'payment_plan'     => 'full',
                 'fulfillment_type' => 'pickup',
                 // no custom_terms_agreed
             ]);

        $response->assertSessionHasNoErrors();

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertFalse((bool) $order->custom_terms_agreed);
    }

    // ─── 6. Order model casts ────────────────────────────────────────────────

    /** @test */
    public function order_custom_terms_agreed_stores_and_casts_boolean_true(): void
    {
        $order = Order::create([
            'order_number'       => 'GS-TEST-001',
            'status'             => 'pending',
            'payment_status'     => 'unpaid',
            'payment_plan'       => 'full',
            'is_custom_order'    => true,
            'custom_terms_agreed'=> true,
            'total_price'        => 500.00,
            'total_amount'       => 500.00,
            'fulfillment_type'   => 'pickup',
        ]);

        $fresh = $order->fresh();
        $this->assertIsBool($fresh->custom_terms_agreed);
        $this->assertTrue($fresh->custom_terms_agreed);
    }

    /** @test */
    public function order_custom_terms_agreed_defaults_to_false(): void
    {
        $order = Order::create([
            'order_number'     => 'GS-TEST-002',
            'status'           => 'pending',
            'payment_status'   => 'unpaid',
            'payment_plan'     => 'full',
            'is_custom_order'  => false,
            'total_price'      => 300.00,
            'total_amount'     => 300.00,
            'fulfillment_type' => 'pickup',
        ]);

        $this->assertFalse((bool) $order->fresh()->custom_terms_agreed);
    }

    // ─── 7. Super Admin product controller ──────────────────────────────────

    /** @test */
    public function super_admin_can_enable_requires_custom_terms_on_product(): void
    {
        $superAdmin = User::create([
            'name'              => 'Super Admin',
            'email'             => 'sa@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'super_admin',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $product = $this->standardProduct;

        $this->actingAs($superAdmin)
             ->put(route('super-admin.products.update', $product->id), [
                 'name'                  => $product->name,
                 'category'              => $product->category,
                 'price'                 => $product->price,
                 'is_active'             => '1',
                 'requires_design_file'  => '0',
                 'requires_custom_terms' => '1',
             ])
             ->assertSessionHasNoErrors();

        $this->assertTrue((bool) $product->fresh()->requires_custom_terms);
    }

    /** @test */
    public function super_admin_can_disable_requires_custom_terms_on_product(): void
    {
        $superAdmin = User::create([
            'name'              => 'Super Admin',
            'email'             => 'sa2@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'super_admin',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $product = $this->customTermsProduct;

        $this->actingAs($superAdmin)
             ->put(route('super-admin.products.update', $product->id), [
                 'name'                  => $product->name,
                 'category'              => $product->category,
                 'price'                 => $product->price,
                 'is_active'             => '1',
                 'requires_design_file'  => '0',
                 'requires_custom_terms' => '0',
             ])
             ->assertSessionHasNoErrors();

        $this->assertFalse((bool) $product->fresh()->requires_custom_terms);
    }
}