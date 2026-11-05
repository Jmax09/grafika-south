<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerRegistrationAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_customer_can_register_and_is_automatically_logged_in_with_customer_role()
    {
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'middle_name' => 'Sample',
            'last_name' => 'Customer',
            'date_of_birth' => '2000-01-01',
            'email' => 'testcustomer@example.com',
            'phone' => '09129998877',
            'contact' => '09129998877',
            'address' => 'San Pedro, Laguna',
            'region' => 'Region IV-A',
            'province' => 'Laguna',
            'city' => 'San Pedro',
            'barangay' => 'San Vicente',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/email/verify');
        $this->assertAuthenticated();

        $user = User::where('email', 'testcustomer@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('customer', $user->role);
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_guest_is_guarded_and_redirected_to_login_when_accessing_checkout()
    {
        $response = $this->get('/checkout');

        $response->assertRedirect('/login');
        $response->assertSessionHas('warning');
        $this->assertEquals(route('checkout.index'), session('url.intended'));
    }

    public function test_guest_can_add_item_to_cart_and_placing_order_after_login_saves_order_under_user_id()
    {
        $product = Product::first();

        // 1. Guest adds item to cart
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertRedirect('/cart');

        $this->assertCount(1, session('cart'));

        // 2. Guest tries checkout -> redirected to login
        $this->get('/checkout')->assertRedirect('/login');

        // 3. Customer logs in
        $customer = User::create([
            'name' => 'John Customer',
            'email' => 'johncust@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'is_active' => true,
        ]);
        $this->actingAs($customer);

        // 4. Submit order
        $orderResponse = $this->post('/checkout', [
            'payment_method' => 'GCash',
            'notes' => 'Test order instructions',
        ]);

        $order = Order::where('user_id', $customer->id)->first();
        $this->assertNotNull($order);


        $orderResponse->assertRedirect(route('customer.orders.show', $order->id));

        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_customer_can_view_order_history_on_dashboard()
    {
        $customer = User::create([
            'name' => 'Jane Customer',
            'email' => 'janecust@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $product = Product::first();

        $order = Order::create([
            'order_number' => 'GS-TEST-1001',
            'user_id' => $customer->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'total_price' => 500.00,
            'total_amount' => 500.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 500.00,
            'subtotal' => 500.00,
        ]);

        $response = $this->actingAs($customer)->get('/customer/dashboard');

        $response->assertStatus(200);
        $response->assertSee('GS-TEST-1001');
        $response->assertSee($product->name);
    }

    public function test_customer_orders_dashboard_renders_clean_pagination_controls()
    {
        $customer = User::create([
            'name' => 'Paginated Customer',
            'email' => 'pagecust@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $product = Product::first();

        // Create 8 orders so pagination is triggered (5 per page)
        for ($i = 1; $i <= 8; $i++) {
            $order = Order::create([
                'order_number' => 'GS-PAGE-' . (1000 + $i),
                'user_id' => $customer->id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'total_price' => 100.00 * $i,
                'total_amount' => 100.00 * $i,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'unit_price' => 100.00 * $i,
                'subtotal' => 100.00 * $i,
            ]);
        }

        $response = $this->actingAs($customer)->get('/customer/dashboard');

        $response->assertStatus(200);
        $response->assertSee('orders-pagination-container', false);
        $response->assertSee('Showing', false);
        $response->assertSee('results', false);
        $response->assertSee('pagination-btn', false);
        $response->assertSee('pagination-numbers', false);
        $response->assertSee('fa-chevron-left', false);
        $response->assertSee('fa-chevron-right', false);
    }

    public function test_timezone_is_configured_to_asia_manila()
    {
        $this->assertEquals('Asia/Manila', config('app.timezone'));
    }

    public function test_order_support_messages_render_timestamps_in_philippine_standard_time()
    {
        $customer = User::create([
            'name' => 'Chat Customer',
            'email' => 'chatcust@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $order = Order::create([
            'order_number' => 'GS-TIME-001',
            'user_id' => $customer->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'total_price' => 250.00,
            'total_amount' => 250.00,
        ]);

        // Set created_at to a known point: 2026-08-17 16:30:00 Asia/Manila (4:30 PM PHT)
        $message = \App\Models\OrderMessage::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'message' => 'Can you please check my artwork dimensions?',
        ]);
        $message->created_at = \Carbon\Carbon::create(2026, 8, 17, 16, 30, 0, 'Asia/Manila');
        $message->save();

        $this->assertEquals('Aug 17, 04:30 PM', $message->formatted_pht_time);

        $response = $this->actingAs($customer)->get(route('customer.orders.show', $order->id));
        $response->assertStatus(200);
        $response->assertSee('Aug 17, 04:30 PM');
    }

    public function test_admin_can_place_walkin_order_with_only_first_name_for_store_pickup()
    {
        $admin = User::create([
            'name' => 'Admin Staff',
            'email' => 'adminstaff@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->actingAs($admin);

        $product = Product::first();

        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect('/cart');

        // Admin submits with only walkin_first_name (all other fields omitted for store pickup)
        $response = $this->post('/checkout', [
            'fulfillment_type' => 'pickup',
            'walkin_first_name' => 'Maria',
        ]);

        $order = Order::where('walkin_first_name', 'Maria')->first();
        $this->assertNotNull($order);
        $this->assertTrue((bool)$order->is_walkin);
        $this->assertEquals('pickup', $order->fulfillment_type);
        $this->assertNull($order->user_id);
        $this->assertNull($order->walkin_last_name);
        $this->assertNull($order->walkin_address);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('Maria', $order->customer_name);

        $response->assertRedirect(route('admin.orders.show', $order->id));
        $response->assertSessionHas('auto_print_invoice', route('admin.orders.invoice', $order->id));
    }

    public function test_super_admin_checkout_is_automatically_walkin_cash_and_paid()
    {
        $superAdmin = User::create([
            'name' => 'Super Administrator User',
            'email' => 'superadminuser@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $this->actingAs($superAdmin);

        $product = Product::first();

        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect('/cart');

        $response = $this->post('/checkout', [
            'walkin_first_name' => 'Carlos',
        ]);

        $order = Order::where('walkin_first_name', 'Carlos')->first();
        $this->assertNotNull($order);
        $this->assertTrue((bool)$order->is_walkin);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('Cash', $order->payment->payment_method);
        $this->assertEquals('verified', $order->payment->status);

        $response->assertRedirect(route('admin.orders.show', $order->id));
        $response->assertSessionHas('auto_print_invoice', route('admin.orders.invoice', $order->id));
    }

    public function test_walkin_checkout_page_shows_payment_method_selector()
    {
        $admin = User::create([
            'name' => 'POS Selector Admin',
            'email' => 'posselectoradmin@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->actingAs($admin);

        $product = Product::first();
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect('/cart');

        $response = $this->get('/checkout');
        $response->assertStatus(200);
        // Staff POS shows compact payment type notification with toggle buttons
        $response->assertSee('Payment Type:');
        $response->assertSee('walkin_pay_notif');
        $response->assertSee('walkin-pay-toggle');
        $response->assertSee('walkin_pay_label');
    }

    public function test_admin_can_record_online_payment_for_walkin_order()
    {
        $admin = User::create([
            'name' => 'POS Online Admin',
            'email' => 'posonlineadmin@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->actingAs($admin);

        $product = Product::first();
        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect('/cart');

        // Staff marks the counter order as paid via GCash (no screenshot needed)
        $response = $this->post('/checkout', [
            'fulfillment_type' => 'pickup',
            'walkin_first_name' => 'Nena',
            'payment_method' => 'GCash',
            'payment_plan' => 'full',
        ]);

        $order = Order::where('walkin_first_name', 'Nena')->first();
        $this->assertNotNull($order);
        $this->assertTrue((bool) $order->is_walkin);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('GCash', $order->payment->payment_method);
        $this->assertEquals('verified', $order->payment->status);

        $response->assertRedirect(route('admin.orders.show', $order->id));
    }

    public function test_walkin_delivery_strictly_requires_all_customer_fields()
    {
        $admin = User::create([
            'name' => 'Delivery Admin',
            'email' => 'deliveryadmin@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->actingAs($admin);

        $product = Product::first();

        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect('/cart');

        // Missing last_name, email, contact_number, address on delivery must fail validation
        $response = $this->post('/checkout', [
            'fulfillment_type' => 'delivery',
            'walkin_first_name' => 'Juan',
        ]);

        $response->assertSessionHasErrors(['walkin_last_name', 'walkin_email', 'walkin_contact_number', 'walkin_address']);

        // Providing all fields on delivery must succeed
        $successResponse = $this->post('/checkout', [
            'fulfillment_type' => 'delivery',
            'walkin_first_name' => 'Juan',
            'walkin_last_name' => 'dela Cruz',
            'walkin_email' => 'juan@example.com',
            'walkin_contact_number' => '09171234567',
            'walkin_address' => 'Unit 101, Rizal St, San Pedro, Laguna',
        ]);

        $order = Order::where('walkin_first_name', 'Juan')->where('walkin_last_name', 'dela Cruz')->first();
        $this->assertNotNull($order);
        $this->assertEquals('delivery', $order->fulfillment_type);
        $this->assertEquals('juan@example.com', $order->customer_email);
        $this->assertEquals('09171234567', $order->customer_contact);
        $this->assertEquals('Unit 101, Rizal St, San Pedro, Laguna', $order->customer_address);
        $successResponse->assertRedirect(route('admin.orders.show', $order->id));
    }

    public function test_checkout_supports_multiple_artwork_file_uploads()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $customer = User::create([
            'name' => 'Design Customer',
            'email' => 'designcust@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'is_active' => true,
        ]);
        $this->actingAs($customer);

        $product = Product::first();

        $this->post('/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect('/cart');

        $cartKey = array_key_first(session('cart'));

        $file1 = \Illuminate\Http\UploadedFile::fake()->create('front-design.png', 500, 'image/png');
        $file2 = \Illuminate\Http\UploadedFile::fake()->create('back-vector.pdf', 100, 'application/pdf');

        $response = $this->post('/checkout', [
            'payment_method' => 'GCash',
            'item_files' => [
                $cartKey => [$file1, $file2],
            ],
        ]);

        $order = Order::where('user_id', $customer->id)->first();
        $this->assertNotNull($order);
        $this->assertCount(1, $order->items);

        $orderItem = $order->items->first();
        $this->assertCount(2, $orderItem->files);
    }

    public function test_admin_can_view_printable_invoice_for_walkin_order()
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin2@example.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $this->actingAs($admin);

        $product = Product::first();

        $order = Order::create([
            'order_number' => 'GS-WALKIN-001',
            'user_id' => null,
            'is_walkin' => true,
            'fulfillment_type' => 'pickup',
            'walkin_first_name' => 'Pedro',
            'walkin_last_name' => 'Penduko',
            'walkin_email' => 'pedro@example.com',
            'walkin_contact_number' => '09199998888',
            'walkin_address' => 'San Pedro, Laguna',
            'status' => 'pending',
            'payment_status' => 'paid',
            'total_price' => 300.00,
            'total_amount' => 300.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 150.00,
            'subtotal' => 300.00,
        ]);

        $response = $this->get(route('admin.orders.invoice', $order->id));
        $response->assertStatus(200);
        $response->assertSee('GS-WALKIN-001');
        $response->assertSee('Pedro Penduko');
        $response->assertSee('OFFICIAL INVOICE / RECEIPT');
    }
}
