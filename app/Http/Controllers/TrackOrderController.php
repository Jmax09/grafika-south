<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class TrackOrderController extends Controller
{
    /**
     * Display the Track & Trace order status page.
     */
    public function index(Request $request)
    {
        $orderNumber = $request->query('order_number');
        $order = null;
        $searched = false;

        if (!empty($orderNumber)) {
            $searched = true;
            $cleanNumber = strtoupper(trim(ltrim($orderNumber, '#')));

            $order = Order::with([
                'items.product',
                'items.files',
                'payments',
                'payment',
                'subOrders.items.product',
                'subOrders.items.files',
                'subOrders.payments',
                'subOrders.payment',
                'parentOrder.subOrders.items.product',
                'parentOrder.subOrders.payment',
            ])
                ->where('order_number', $cleanNumber)
                ->orWhere('order_number', 'GS-' . $cleanNumber)
                ->first();
        }

        return view('orders.track', compact('order', 'orderNumber', 'searched'));
    }
}
