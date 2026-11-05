<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartVoucherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_cart_page_renders_voucher_input_and_summary()
    {
        $product = Product::where('is_active', true)->first();

        $cart = [
            'key_1' => [
                'cart_key' => 'key_1',
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => 200.00,
                'quantity' => 2,
                'selected' => true,
            ]
        ];

        $response = $this->withSession(['cart' => $cart])->get(route('cart.index'));

        $response->assertStatus(200);
        $response->assertSee('Order Estimate Summary');
        $response->assertSee('voucher_code_input', false);
        $response->assertSee('btn-apply-voucher', false);
        $response->assertSee('summary-discount-row', false);
    }

    public function test_applying_invalid_voucher_returns_error()
    {
        $cart = [
            'key_1' => [
                'cart_key' => 'key_1',
                'product_id' => 1,
                'name' => 'Item 1',
                'price' => 500.00,
                'quantity' => 1,
                'selected' => true,
            ]
        ];

        $response = $this->withSession(['cart' => $cart])->postJson(route('cart.apply-voucher'), [
            'code' => 'NONEXISTENTCODE',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertNull(session('applied_voucher'));
    }

    public function test_applying_inactive_or_expired_voucher_is_rejected()
    {
        $inactivePromo = Promotion::create([
            'code' => 'INACTIVE10',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'min_spend' => 0,
            'is_active' => false,
        ]);

        $expiredPromo = Promotion::create([
            'code' => 'EXPIRED20',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'min_spend' => 0,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $cart = [
            'key_1' => [
                'cart_key' => 'key_1',
                'product_id' => 1,
                'name' => 'Item 1',
                'price' => 500.00,
                'quantity' => 1,
                'selected' => true,
            ]
        ];

        // Try inactive
        $resInactive = $this->withSession(['cart' => $cart])->postJson(route('cart.apply-voucher'), [
            'code' => 'INACTIVE10',
        ]);
        $resInactive->assertStatus(422);
        $this->assertNull(session('applied_voucher'));

        // Try expired
        $resExpired = $this->withSession(['cart' => $cart])->postJson(route('cart.apply-voucher'), [
            'code' => 'EXPIRED20',
        ]);
        $resExpired->assertStatus(422);
        $this->assertNull(session('applied_voucher'));
    }

    public function test_applying_voucher_with_unmet_min_spend_is_rejected()
    {
        Promotion::create([
            'code' => 'MIN1000',
            'discount_type' => 'fixed',
            'discount_value' => 100,
            'min_spend' => 1000.00,
            'is_active' => true,
        ]);

        $cart = [
            'key_1' => [
                'cart_key' => 'key_1',
                'product_id' => 1,
                'name' => 'Item 1',
                'price' => 400.00,
                'quantity' => 1,
                'selected' => true,
            ]
        ];

        $response = $this->withSession(['cart' => $cart])->postJson(route('cart.apply-voucher'), [
            'code' => 'MIN1000',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertNull(session('applied_voucher'));
    }

    public function test_applying_percentage_discount_voucher_calculates_correct_discount()
    {
        Promotion::create([
            'code' => 'SAVE20',
            'discount_type' => 'percentage',
            'discount_value' => 20.00,
            'min_spend' => 200.00,
            'is_active' => true,
        ]);

        // Subtotal = 500.00
        $cart = [
            'key_1' => [
                'cart_key' => 'key_1',
                'product_id' => 1,
                'name' => 'Item 1',
                'price' => 250.00,
                'quantity' => 2,
                'selected' => true,
            ]
        ];

        $response = $this->withSession(['cart' => $cart])->postJson(route('cart.apply-voucher'), [
            'code' => 'save20', // Case insensitivity test
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'subtotal' => 500.00,
            'discount' => 100.00,
            'total' => 400.00,
            'formatted_discount' => '-₱100.00',
            'formatted_total' => '₱400.00',
        ]);

        $applied = session('applied_voucher');
        $this->assertNotNull($applied);
        $this->assertEquals('SAVE20', $applied['code']);
    }

    public function test_applying_fixed_discount_voucher_calculates_correct_discount()
    {
        Promotion::create([
            'code' => 'LESS150',
            'discount_type' => 'fixed',
            'discount_value' => 150.00,
            'min_spend' => 300.00,
            'is_active' => true,
        ]);

        // Subtotal = 600.00
        $cart = [
            'key_1' => [
                'cart_key' => 'key_1',
                'product_id' => 1,
                'name' => 'Item 1',
                'price' => 200.00,
                'quantity' => 3,
                'selected' => true,
            ]
        ];

        $response = $this->withSession(['cart' => $cart])->postJson(route('cart.apply-voucher'), [
            'code' => 'LESS150',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'subtotal' => 600.00,
            'discount' => 150.00,
            'total' => 450.00,
            'formatted_discount' => '-₱150.00',
            'formatted_total' => '₱450.00',
        ]);
    }

    public function test_removing_voucher_clears_discount_and_restores_total()
    {
        $promo = Promotion::create([
            'code' => 'PROMO50',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'min_spend' => 0,
            'is_active' => true,
        ]);

        $cart = [
            'key_1' => [
                'cart_key' => 'key_1',
                'product_id' => 1,
                'name' => 'Item 1',
                'price' => 300.00,
                'quantity' => 1,
                'selected' => true,
            ]
        ];

        $response = $this->withSession([
            'cart' => $cart,
            'applied_voucher' => [
                'id' => $promo->id,
                'code' => $promo->code,
                'discount_type' => $promo->discount_type,
                'discount_value' => 50.00,
                'min_spend' => 0,
            ],
        ])->postJson(route('cart.remove-voucher'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'subtotal' => 300.00,
            'discount' => 0,
            'total' => 300.00,
        ]);

        $this->assertNull(session('applied_voucher'));
    }

    public function test_toggling_items_recalculates_applied_voucher_and_checks_min_spend()
    {
        $promo = Promotion::create([
            'code' => 'DISC10PCT',
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'min_spend' => 300.00,
            'is_active' => true,
        ]);

        $cart = [
            'key_1' => [
                'cart_key' => 'key_1',
                'product_id' => 1,
                'name' => 'Item 1',
                'price' => 200.00,
                'quantity' => 1,
                'selected' => true,
            ],
            'key_2' => [
                'cart_key' => 'key_2',
                'product_id' => 2,
                'name' => 'Item 2',
                'price' => 200.00,
                'quantity' => 1,
                'selected' => true,
            ],
        ];

        // Total 400.00 -> 10% = 40.00 discount, total = 360.00
        // Deselect item 2 -> Subtotal 200.00 -> Under 300 min_spend -> discount = 0, total = 200.00
        $response = $this->withSession([
            'cart' => $cart,
            'applied_voucher' => [
                'id' => $promo->id,
                'code' => $promo->code,
                'discount_type' => $promo->discount_type,
                'discount_value' => 10.00,
                'min_spend' => 300.00,
            ],
        ])->postJson(route('cart.toggle-select'), [
            'key' => 'key_2',
            'selected' => false,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'subtotal' => 200.00,
            'discount' => 0,
            'total' => 200.00,
        ]);
        $this->assertNotNull($response->json('voucher_error'));
    }
}
