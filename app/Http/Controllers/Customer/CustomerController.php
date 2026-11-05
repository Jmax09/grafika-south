<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display Customer Dashboard & Order History.
     */
    public function dashboard()
    {
        $user = Auth::user();

        // Fetch customer's order history with associated order items & products
        $orders = $user ? $user->orders()->with('items.product')->latest()->get() : collect();
        $products = Product::where('status', 'active')->take(6)->get();

        return view('customer.dashboard', compact('user', 'orders', 'products'));
    }
}
