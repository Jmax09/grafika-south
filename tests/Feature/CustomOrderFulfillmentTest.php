<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomOrderFulfillmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Product $customProduct;
    protected Product $standardProduct;
    protected PaymentMethod $gcash;
    protected PaymentMethod $cashOnPickup;

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

        $this->gcash = PaymentMethod::create([
            'name'              => 'GCash',
            'type'              => 'manual',
            'is_online_payment' => true,
            'is_active'         => true,
        ]);

        $this->cashOnPickup = PaymentMethod::create([
            'name'              => 'Cash - Pay upon Store Collection',
            'type'              => 'cash',
            'is_online_payment' => false,
            'is_active'         => true,
        ]);

        $this->customProduct = Product::create([
            'name'                  => 'Custom Neon Sign',
            'slug'                  => 'custom-neon-sign',
            'category'              => 'Signage',
            'price'                 => 2500.00,
            'base_price'            => 2500.00,
            'requires_design_file'  => true,
            'requires_custom_terms' => true,
            'is_active'             => true,
        ]);

        $this->standardProduct = Product::create([
            'name'                  => 'Standard Ballpen',
            'slug'                  => 'standard-ballpen',
            'category'              => 'Stationery',
            'price'                 => 50.00,
            'base_price'            => 50.00,
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

    #[Test]
    public function checkout_page_shows_both_cards_for_custom_cart(): void
    {
        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customProduct)])
             ->get(route('checkout.index'));

        $response->assertOk();
        $response->assertSee('id="option_pickup_card"', false);
        $response->assertSee('id="option_delivery_card"', false);
        $response->assertDontSee('id="option_pickup_card_disabled"', false);
        $response->assertDontSee('Unavailable - Custom items require courier dispatch');
        $response->assertSee('id="store_pickup_notice"', false);
        $response->assertViewHas('allowedFulfillmentTypes', ['pickup', 'delivery']);
    }

    #[Test]
    public function checkout_page_shows_both_options_for_standard_cart(): void
    {
        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->standardProduct)])
             ->get(route('checkout.index'));

        $response->assertOk();
        $response->assertSee('id="option_pickup_card"', false);
        $response->assertSee('id="option_delivery_card"', false);
        $response->assertViewHas('allowedFulfillmentTypes', ['pickup', 'delivery']);
    }

    #[Test]
    public function checkout_page_hides_cash_payment_methods_for_custom_cart(): void
    {
        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customProduct)])
             ->get(route('checkout.index'));

        $response->assertOk();
        $response->assertDontSee($this->cashOnPickup->name);
        $response->assertSee($this->gcash->name);
    }

    #[Test]
    public function checkout_accepts_store_pickup_with_online_payment_for_custom_order(): void
    {
        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customProduct)])
             ->post(route('checkout.store'), [
                 'fulfillment_type'    => 'pickup',
                 'payment_method'      => $this->gcash->name,
                 'payment_plan'        => 'full',
                 'custom_terms_agreed' => '1',
             ]);

        $response->assertSessionHasNoErrors();
        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('pickup', $order->fulfillment_type);
    }

    #[Test]
    public function checkout_rejects_cash_payment_for_custom_order_regardless_of_fulfillment(): void
    {
        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customProduct)])
             ->post(route('checkout.store'), [
                 'fulfillment_type'    => 'pickup',
                 'payment_method'      => $this->cashOnPickup->name,
                 'payment_plan'        => 'full',
                 'custom_terms_agreed' => '1',
             ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    #[Test]
    public function checkout_accepts_delivery_for_custom_order(): void
    {
        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customProduct)])
             ->post(route('checkout.store'), [
                 'fulfillment_type'    => 'delivery',
                 'payment_method'      => $this->gcash->name,
                 'payment_plan'        => 'full',
                 'custom_terms_agreed' => '1',
             ]);

        $response->assertSessionHasNoErrors();
        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('delivery', $order->fulfillment_type);
    }

    #[Test]
    public function checkout_accepts_store_pickup_for_standard_order(): void
    {
        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->standardProduct)])
             ->post(route('checkout.store'), [
                 'fulfillment_type'    => 'pickup',
                 'payment_method'      => $this->gcash->name,
                 'payment_plan'        => 'full',
             ]);

        $response->assertSessionHasNoErrors();
        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('pickup', $order->fulfillment_type);
    }

    #[Test]
    public function checkout_rejects_invalid_fulfillment_type(): void
    {
        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customProduct)])
             ->post(route('checkout.store'), [
                 'fulfillment_type'    => 'invalid_drone_delivery',
                 'payment_method'      => $this->gcash->name,
                 'payment_plan'        => 'full',
                 'custom_terms_agreed' => '1',
             ]);

        $response->assertSessionHasErrors('fulfillment_type');
        $this->assertDatabaseCount('orders', 0);
    }

    #[Test]
    public function checkout_page_hides_cash_methods_when_product_has_requires_custom_terms_only(): void
    {
        $customTermsProduct = Product::create([
            'name'                  => 'Terms Only Custom Item',
            'slug'                  => 'terms-only-custom-item',
            'category'              => 'Apparel',
            'price'                 => 500.00,
            'base_price'            => 500.00,
            'requires_design_file'  => false,
            'requires_custom_terms' => true,
            'is_active'             => true,
        ]);

        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($customTermsProduct)])
             ->get(route('checkout.index'));

        $response->assertOk();
        $response->assertDontSee($this->cashOnPickup->name);
        $response->assertSee($this->gcash->name);
    }
}