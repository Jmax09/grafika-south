<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display the Admin & Super Admin Dashboard.
     */
    public function dashboard()
    {
        $ordersToday = Order::whereDate('created_at', today())->count();
        $inProduction = Order::whereIn('status', ['in_production', 'pending'])->count();
        
        $materials = Material::latest()->get();
        $lowStockMaterials = $materials
            ->filter(fn($m) => $m->isLowStock())
            ->sortBy(fn($m) => (float) $m->current_stock)
            ->values();
        $lowStock = $lowStockMaterials->count();

        $revenueThisMonth = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $totalOrders = Order::count();
        $totalProducts = Product::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $recentOrders = Order::with('user')->latest()->take(6)->get();

        // Fetch all staff admin & super admin accounts for Super Admin management
        $adminUsers = User::whereIn('role', ['admin', 'super_admin'])->get();

        return view('admin.dashboard', compact(
            'ordersToday',
            'inProduction',
            'lowStock',
            'lowStockMaterials',
            'revenueThisMonth',
            'totalOrders',
            'totalProducts',
            'totalCustomers',
            'recentOrders',
            'adminUsers',
            'materials'
        ));
    }
}
