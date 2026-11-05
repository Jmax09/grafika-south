<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserCart;
use App\Services\CartAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAccountPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_is_saved_to_the_customer_account(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user);

        session()->put('cart', [
            'prod_1_abc' => [
                'cart_key'   => 'prod_1_abc',
                'product_id' => 1,
                'name'        => 'Test Product',
                'price'       => 350,
                'quantity'    => 2,
                'selected'    => true,
                'design_file_paths' => ['order_files_temp/a.pdf', 'order_files_temp/b.pdf'],
            ],
        ]);

        CartAccountService::persistToAccount();

        $this->assertDatabaseHas('user_carts', ['user_id' => $user->id]);
        $saved = UserCart::where('user_id', $user->id)->first();
        $this->assertCount(1, $saved->items);
        $this->assertSame(
            ['order_files_temp/a.pdf', 'order_files_temp/b.pdf'],
            $saved->items['prod_1_abc']['design_file_paths']
        );
    }

    public function test_saved_cart_is_restored_into_a_new_session(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user);

        session()->put('cart', [
            'prod_1_abc' => [
                'cart_key'   => 'prod_1_abc',
                'product_id' => 1,
                'name'        => 'Test Product',
                'price'       => 350,
                'quantity'    => 2,
                'selected'    => true,
            ],
        ]);
        CartAccountService::persistToAccount();

        // Simulate a brand-new session (cookie/session id changed)
        session()->forget('cart');
        $this->assertEmpty(session()->get('cart', []));

        CartAccountService::hydrateFromAccount();

        $restored = session()->get('cart', []);
        $this->assertCount(1, $restored);
        $this->assertSame('Test Product', $restored['prod_1_abc']['name']);
    }

    public function test_clearing_the_cart_removes_it_from_the_account(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user);

        session()->put('cart', [
            'prod_1_abc' => ['cart_key' => 'prod_1_abc', 'product_id' => 1],
        ]);
        CartAccountService::persistToAccount();
        $this->assertDatabaseHas('user_carts', ['user_id' => $user->id]);

        session()->forget('cart');
        CartAccountService::forgetAccountCart();

        $this->assertDatabaseMissing('user_carts', ['user_id' => $user->id]);
    }
}
