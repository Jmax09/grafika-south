<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderChatUxTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_messages_are_ordered_chronologically_ascending_in_relationship(): void
    {
        $customer = User::where('role', 'customer')->first();
        $order = Order::create([
            'order_number' => 'GS-CHAT-001',
            'user_id' => $customer->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'total_price' => 500,
            'total_amount' => 500,
        ]);

        $firstMsg = OrderMessage::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'message' => 'First message (earliest)',
        ]);
        $firstMsg->created_at = Carbon::now()->subMinutes(10);
        $firstMsg->save();

        $secondMsg = OrderMessage::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'message' => 'Second message (latest)',
        ]);
        $secondMsg->created_at = Carbon::now()->subMinutes(2);
        $secondMsg->save();

        // Refresh relationship
        $order->load('messages');

        $this->assertCount(2, $order->messages);
        $this->assertEquals('First message (earliest)', $order->messages->first()->message);
        $this->assertEquals('Second message (latest)', $order->messages->last()->message);
    }

    public function test_customer_can_send_message_via_ajax_and_receive_json(): void
    {
        $customer = User::where('role', 'customer')->first();
        $order = Order::create([
            'order_number' => 'GS-CHAT-002',
            'user_id' => $customer->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'total_price' => 500,
            'total_amount' => 500,
        ]);

        $response = $this->actingAs($customer)->postJson(route('customer.orders.chat', $order->id), [
            'message' => 'Hello staff, do you have sample prints?',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Message sent to staff.',
            'data' => [
                'message' => 'Hello staff, do you have sample prints?',
                'sender_name' => $customer->name,
                'is_me' => true,
            ]
        ]);

        $this->assertDatabaseHas('order_messages', [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'message' => 'Hello staff, do you have sample prints?',
        ]);
    }

    public function test_admin_can_send_message_via_ajax_and_receive_json(): void
    {
        $customer = User::where('role', 'customer')->first();
        $admin = User::where('role', 'admin')->first();
        $order = Order::create([
            'order_number' => 'GS-CHAT-003',
            'user_id' => $customer->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'total_price' => 500,
            'total_amount' => 500,
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.orders.chat', $order->id), [
            'message' => 'Yes, we will ship the proofs tomorrow!',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Message sent to customer.',
            'data' => [
                'message' => 'Yes, we will ship the proofs tomorrow!',
                'sender_name' => $admin->name . ' (admin)',
                'is_staff' => true,
            ]
        ]);

        $this->assertDatabaseHas('order_messages', [
            'order_id' => $order->id,
            'user_id' => $admin->id,
            'message' => 'Yes, we will ship the proofs tomorrow!',
        ]);
    }

    public function test_customer_order_detail_view_renders_chat_elements_and_scripts(): void
    {
        $customer = User::where('role', 'customer')->first();
        $order = Order::create([
            'order_number' => 'GS-CHAT-004',
            'user_id' => $customer->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'total_price' => 500,
            'total_amount' => 500,
        ]);

        $response = $this->actingAs($customer)->get(route('customer.orders.show', $order->id));
        $response->assertStatus(200);
        $response->assertSee('id="orderChatMessages"', false);
        $response->assertSee('id="orderChatForm"', false);
        $response->assertSee('id="chatMessageInput"', false);
        $response->assertSee('id="chatSendBtn"', false);
        $response->assertSee('scrollToBottom', false);
    }
}
