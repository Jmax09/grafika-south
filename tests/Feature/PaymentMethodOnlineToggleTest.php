<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentMethodOnlineToggleTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $customer;
    protected Product $customProduct;
    protected Product $standardProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::create([
            'name'              => 'Super Admin User',
            'email'             => 'superadmin@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'super_admin',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $this->customer = User::create([
            'name'              => 'Customer User',
            'email'             => 'customer@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'customer',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $this->customProduct = Product::create([
            'name'                  => 'Custom Acrylic Sign',
            'slug'                  => 'custom-acrylic-sign',
            'category'              => 'Signage',
            'price'                 => 1200.00,
            'base_price'            => 1200.00,
            'requires_design_file'  => true,
            'requires_custom_terms' => false,
            'is_active'             => true,
        ]);

        $this->standardProduct = Product::create([
            'name'                  => 'Ready-Made Notebook',
            'slug'                  => 'ready-made-notebook',
            'category'              => 'Stationery',
            'price'                 => 150.00,
            'base_price'            => 150.00,
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

    // ─── 1. Model Attributes & Helpers ────────────────────────────────────────

    /** @test */
    public function payment_method_casts_is_online_payment_to_boolean(): void
    {
        $onlineMethod = PaymentMethod::create([
            'name'              => 'GCash Express',
            'type'              => 'manual',
            'is_online_payment' => true,
            'is_active'         => true,
        ]);

        $offlineMethod = PaymentMethod::create([
            'name'              => 'In-Store Cash Counter',
            'type'              => 'manual',
            'is_online_payment' => false,
            'is_active'         => true,
        ]);

        $this->assertIsBool($onlineMethod->fresh()->is_online_payment);
        $this->assertTrue($onlineMethod->fresh()->is_online_payment);
        $this->assertTrue($onlineMethod->fresh()->isDigitalMethod());
        $this->assertFalse($onlineMethod->fresh()->isCashOrOfflineMethod());

        $this->assertIsBool($offlineMethod->fresh()->is_online_payment);
        $this->assertFalse($offlineMethod->fresh()->is_online_payment);
        $this->assertFalse($offlineMethod->fresh()->isDigitalMethod());
        $this->assertTrue($offlineMethod->fresh()->isCashOrOfflineMethod());
    }

    // ─── 2. Super Admin Controller Store & Update ───────────────────────────

    /** @test */
    public function super_admin_can_create_online_payment_method(): void
    {
        $response = $this->actingAs($this->superAdmin)
             ->post(route('super-admin.payment-methods.store'), [
                 'name'              => 'Maya QR',
                 'type'              => 'manual',
                 'account_name'      => 'Grafika South Maya',
                 'account_number'    => '0917-123-4567',
                 'instructions'      => 'Scan Maya QR to pay.',
                 'is_online_payment' => '1',
                 'is_active'         => '1',
             ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $method = PaymentMethod::where('name', 'Maya QR')->first();
        $this->assertNotNull($method);
        $this->assertTrue((bool) $method->is_online_payment);
    }

    /** @test */
    public function super_admin_can_create_offline_cash_payment_method(): void
    {
        $response = $this->actingAs($this->superAdmin)
             ->post(route('super-admin.payment-methods.store'), [
                 'name'              => 'Over the Counter Cash',
                 'type'              => 'manual',
                 'account_name'      => 'Front Desk',
                 'instructions'      => 'Pay cash upon arrival.',
                 'is_online_payment' => '0',
                 'is_active'         => '1',
             ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $method = PaymentMethod::where('name', 'Over the Counter Cash')->first();
        $this->assertNotNull($method);
        $this->assertFalse((bool) $method->is_online_payment);
    }

    /** @test */
    public function super_admin_can_update_is_online_payment_toggle(): void
    {
        $method = PaymentMethod::create([
            'name'              => 'Custom Transfer',
            'type'              => 'manual',
            'is_online_payment' => true,
            'is_active'         => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
             ->put(route('super-admin.payment-methods.update', $method->id), [
                 'name'              => 'Custom Transfer',
                 'type'              => 'manual',
                 'is_online_payment' => '0',
                 'is_active'         => '1',
             ]);

        $response->assertRedirect();
        $this->assertFalse((bool) $method->fresh()->is_online_payment);

        // Toggle back to online
        $this->actingAs($this->superAdmin)
             ->put(route('super-admin.payment-methods.update', $method->id), [
                 'name'              => 'Custom Transfer',
                 'type'              => 'manual',
                 'is_online_payment' => '1',
                 'is_active'         => '1',
             ]);

        $this->assertTrue((bool) $method->fresh()->is_online_payment);
    }

    // ─── 3. Checkout Page Filtering Based on is_online_payment ──────────────

    /** @test */
    public function checkout_page_filters_out_offline_methods_for_custom_cart(): void
    {
        $onlineMethod = PaymentMethod::create([
            'name'              => 'Bank Transfer Online',
            'type'              => 'manual',
            'is_online_payment' => true,
            'is_active'         => true,
        ]);

        $offlineMethod = PaymentMethod::create([
            'name'              => 'Store Pickup Cash',
            'type'              => 'manual',
            'is_online_payment' => false,
            'is_active'         => true,
        ]);

        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customProduct)])
             ->get(route('checkout.index'));

        $response->assertOk();
        $response->assertSee('Bank Transfer Online');
        $response->assertDontSee('Store Pickup Cash');
    }

    /** @test */
    public function checkout_page_shows_all_active_methods_for_standard_cart(): void
    {
        $onlineMethod = PaymentMethod::create([
            'name'              => 'Bank Transfer Online',
            'type'              => 'manual',
            'is_online_payment' => true,
            'is_active'         => true,
        ]);

        $offlineMethod = PaymentMethod::create([
            'name'              => 'Store Pickup Cash',
            'type'              => 'manual',
            'is_online_payment' => false,
            'is_active'         => true,
        ]);

        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->standardProduct)])
             ->get(route('checkout.index'));

        $response->assertOk();
        $response->assertSee('Bank Transfer Online');
        $response->assertSee('Store Pickup Cash');
    }

    // ─── 4. Checkout Submission Restrictions ─────────────────────────────────

    /** @test */
    public function checkout_rejects_offline_payment_method_for_custom_order(): void
    {
        $offlineMethod = PaymentMethod::create([
            'name'              => 'Special Cash on Pickup',
            'type'              => 'manual',
            'is_online_payment' => false,
            'is_active'         => true,
        ]);

        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customProduct)])
             ->post(route('checkout.store'), [
                 'payment_method'   => $offlineMethod->name,
                 'payment_plan'     => 'full',
                 'fulfillment_type' => 'pickup',
             ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    /** @test */
    public function checkout_accepts_online_payment_method_for_custom_order(): void
    {
        $onlineMethod = PaymentMethod::create([
            'name'              => 'Special GCash Payment',
            'type'              => 'manual',
            'is_online_payment' => true,
            'is_active'         => true,
        ]);

        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customProduct)])
             ->post(route('checkout.store'), [
                 'payment_method'   => $onlineMethod->name,
                 'payment_plan'     => 'full',
                 'fulfillment_type' => 'pickup',
             ]);

        $response->assertSessionHasNoErrors();
        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals($this->customProduct->price, (float) $order->total_amount);
    }

    // ─── 5. Channel Badges & Indicator State Tests ────────────────────────────

    #[Test]
    public function checkout_page_shows_disabled_badge_when_all_cash_methods_turned_off_in_admin(): void
    {
        // Deactivate any pre-seeded cash methods
        PaymentMethod::where('is_online_payment', false)->update(['is_active' => false]);

        // Only online method active
        PaymentMethod::create([
            'name'              => 'GCash Digital',
            'type'              => 'manual',
            'is_online_payment' => true,
            'is_active'         => true,
        ]);

        // Cash method turned OFF
        PaymentMethod::create([
            'name'              => 'Offline Over Counter',
            'type'              => 'manual',
            'is_online_payment' => false,
            'is_active'         => false,
        ]);

        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->standardProduct)])
             ->get(route('checkout.index'));

        $response->assertOk();
        $response->assertSee('Cash / Physical: Disabled in Settings');
        $response->assertDontSee('Offline Over Counter');
    }

    #[Test]
    public function checkout_page_shows_restricted_badge_for_custom_order(): void
    {
        PaymentMethod::create([
            'name'              => 'GCash Digital',
            'type'              => 'manual',
            'is_online_payment' => true,
            'is_active'         => true,
        ]);

        PaymentMethod::create([
            'name'              => 'Offline Store Cash',
            'type'              => 'manual',
            'is_online_payment' => false,
            'is_active'         => true,
        ]);

        $response = $this->actingAs($this->customer)
             ->withSession(['cart' => $this->cartWith($this->customProduct)])
             ->get(route('checkout.index'));

        $response->assertOk();
        $response->assertDontSee('Offline Store Cash');
    }

    #[Test]
    public function super_admin_index_displays_channel_overview_cards_and_badges(): void
    {
        PaymentMethod::create([
            'name'              => 'GCash Express',
            'type'              => 'manual',
            'is_online_payment' => true,
            'is_active'         => true,
        ]);

        PaymentMethod::create([
            'name'              => 'Cash Counter',
            'type'              => 'manual',
            'is_online_payment' => false,
            'is_active'         => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
             ->get(route('super-admin.payment-methods.index'));

        $response->assertOk();
        $response->assertSee('Online / Digital Channel');
        $response->assertSee('Cash / Physical Channel');
        $response->assertSee('Cash / Physical');
        $response->assertSee('Online / Digital');
    }
}