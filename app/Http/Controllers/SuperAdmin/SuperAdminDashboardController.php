<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminDashboardController extends Controller
{
    /** Order statuses that represent confirmed, billable work (excludes pending & cancelled). */
    private const ACTIVE_STATUSES = ['approved', 'design_review', 'printing', 'ready', 'shipped', 'completed'];

    /** Order statuses that still need a human to move them forward. */
    private const OPEN_STATUSES = ['pending', 'design_review', 'approved', 'printing', 'ready'];

    /** Date-range options accepted by the analytics AJAX endpoint. */
    private const RANGE_LABELS = [
        '30d' => 'Last 30 days',
        '90d' => 'Last 90 days',
        '12m' => 'Last 12 months',
        'ytd' => 'Year to date',
        'all' => 'All time',
    ];

    /**
     * Canonical revenue scope: real ("leaf") orders with a confirmed status.
     * Parent orders are rollups of their children, so they are excluded to avoid
     * double-counting revenue across every KPI, chart, and trend.
     */
    private function activeRevenueOrders()
    {
        return Order::whereIn('status', self::ACTIVE_STATUSES)->where($this->leafScope());
    }

    /** A where-closure that keeps only leaf (non-parent) orders. */
    private function leafScope(): \Closure
    {
        return function ($q) {
            $q->where('is_parent', false)->orWhereNull('is_parent');
        };
    }

    /** Apply the global date range to a query that has a created_at column. */
    private function applyRange($query, string $range)
    {
        return match ($range) {
            '30d' => $query->where('created_at', '>=', now()->subDays(30)->startOfDay()),
            '90d' => $query->where('created_at', '>=', now()->subDays(90)->startOfDay()),
            '12m' => $query->where('created_at', '>=', now()->subMonths(12)->startOfDay()),
            'ytd' => $query->where('created_at', '>=', now()->startOfYear()),
            default => $query,
        };
    }

    /**
     * Payment proofs that still need verification.
     *
     * Proofs on a settled or closed order are skipped: staff already dealt with the
     * money (paid / refunded / partially paid) or the order is finished
     * (completed / cancelled), so the proof must not keep inflating "Awaiting
     * verification", the uncollected total or the needs-action queue. A partially-paid
     * order's proof is tracked instead as a downpayment balance. Reopening the order
     * (back to unpaid) brings its proof back.
     */
    private function pendingVerificationProofs()
    {
        return Payment::where('status', 'pending')
            ->whereHas('order', function ($q) {
                $q->whereNotIn('status', ['cancelled', 'completed'])
                    ->whereNotIn('payment_status', ['paid', 'refunded', 'cancelled', 'partially_paid']);
            });
    }

    /**
     * Display Super Admin Full Sales & Analytics Dashboard.
     *
     * All-time by design: the old 30D/90D/12M/YTD/All switcher was removed as
     * redundant, leaving the trend chart's Daily/Weekly/Monthly/Yearly tabs as
     * the single time control.
     */
    public function dashboard()
    {
        $activeStatuses = self::ACTIVE_STATUSES;

        // ── KPI Headline Metrics ──────────────────────────────────────────
        // Revenue is recognised on "leaf" orders only: a parent order's total_price
        // is a rollup of its children, so counting both would double-count revenue.
        $totalRevenue      = (float) $this->activeRevenueOrders()->sum('total_price');
        $scopedOrderCount  = (int)  $this->activeRevenueOrders()->count();
        $monthlyRevenue    = (float) $this->activeRevenueOrders()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_price');
        $revenueLast30Days = (float) $this->activeRevenueOrders()
            ->where('created_at', '>=', now()->subDays(30)->startOfDay())
            ->sum('total_price');
        $ordersLast30Days  = (int) $this->activeRevenueOrders()
            ->where('created_at', '>=', now()->subDays(30)->startOfDay())
            ->count();

        $totalOrdersCount     = Order::whereNull('parent_id')->count();
        $pendingOrdersCount   = Order::where('status', 'pending')->whereNull('parent_id')->count();
        $completedOrdersCount = Order::where('status', 'completed')->whereNull('parent_id')->count();
        $inProgressOrdersCount = Order::whereIn('status', ['approved', 'design_review', 'printing', 'ready', 'shipped'])->whereNull('parent_id')->count();
        $cancelledOrdersCount = Order::where('status', 'cancelled')->whereNull('parent_id')->count();
        $totalCustomersCount = User::where('role', 'customer')->count();
        $totalAdminsCount    = User::whereIn('role', ['admin', 'super_admin'])->count();
        $averageOrderValue   = $scopedOrderCount > 0 ? $totalRevenue / $scopedOrderCount : 0.0;

        // ── Financial / Collection Overview ──────────────────────────────
        // NOTE: payment_status on orders is an enum of unpaid/partially_paid/paid/refunded/cancelled.
        // "Awaiting verification" lives on the payments table (proof of payment uploaded,
        // not yet verified) — reading it from orders.payment_status always returned zero.
        $totalCollected = (float) Payment::where('status', 'verified')->sum('amount');

        $pendingVerificationTotal = (float) $this->pendingVerificationProofs()->sum('amount');
        $pendingVerificationCount = (int) $this->pendingVerificationProofs()->count();
        $pendingProofs = $this->pendingVerificationProofs()
            ->with('order')
            ->latest()
            ->take(10)
            ->get();

        $pendingDownpaymentBalance = Order::whereNotIn('status', ['cancelled'])
            ->whereNotIn('payment_status', ['paid', 'refunded', 'cancelled'])
            ->where('remaining_balance', '>', 0)
            ->where($this->leafScope())
            ->sum('remaining_balance');

        $uncollectedTotal = (float) $pendingDownpaymentBalance + (float) $pendingVerificationTotal;

        $collectedThisMonth = (float) Payment::where('status', 'verified')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('amount');

        // Downpayment balances grouped by how long they have been outstanding.
        $downpaymentAging = Order::whereNotIn('status', ['cancelled'])
            ->whereNotIn('payment_status', ['paid', 'refunded', 'cancelled'])
            ->where('remaining_balance', '>', 0)
            ->where($this->leafScope())
            ->with('user')
            ->orderBy('created_at')
            ->take(10)
            ->get()
            ->map(function ($o) {
                $o->age_days = (int) round($o->created_at->diffInDays(now()));
                return $o;
            });

        // ── Custom vs Regular Split ───────────────────────────────────────
        $customOrdersCount   = Order::where('is_custom_order', true)->whereNull('parent_id')->count();
        $regularOrdersCount  = Order::where('is_custom_order', false)->whereNull('parent_id')->count();
        $customRevenue       = $this->activeRevenueOrders()->where('is_custom_order', true)->sum('total_price');
        $regularRevenue      = $this->activeRevenueOrders()->where('is_custom_order', false)->sum('total_price');

        // ── Top Selling Products (by revenue + by units) ──────────────────
        // Restricted to the same leaf+active scope so totals reconcile with KPIs.
        $topByRevenue = OrderItem::query()
            ->select(
                'order_items.product_id',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', $activeStatuses)
            ->where(function ($q) {
                $q->where('orders.is_parent', false)->orWhereNull('orders.is_parent');
            })
            ->groupBy('order_items.product_id')
            ->with('product')
            ->orderByDesc('total_revenue')
            ->take(10)
            ->get()
            ->filter(fn($i) => $i->product);

        $topByUnits = OrderItem::query()
            ->select(
                'order_items.product_id',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', $activeStatuses)
            ->where(function ($q) {
                $q->where('orders.is_parent', false)->orWhereNull('orders.is_parent');
            })
            ->groupBy('order_items.product_id')
            ->with('product')
            ->orderByDesc('total_sold')
            ->take(10)
            ->get()
            ->filter(fn($i) => $i->product);

        // ── Category Revenue Breakdown ────────────────────────────────────
        $categoryBreakdown = OrderItem::query()
            ->select(
                'products.category',
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('SUM(order_items.quantity) as total_units')
            )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', $activeStatuses)
            ->where(function ($q) {
                $q->where('orders.is_parent', false)->orWhereNull('orders.is_parent');
            })
            ->groupBy('products.category')
            ->orderByDesc('total_revenue')
            ->get();

        // ── Revenue Trend (monthly, last 12 months) ───────────────────────
        $monthlyTrend = $this->buildMonthlyTrend(12);

        // ── Initial chart datasets (monthly, for page-load) ──────────────
        $chartTrendLabels  = $monthlyTrend->pluck('label')->values();
        $chartTrendRevenue = $monthlyTrend->pluck('revenue')->values();
        $chartTrendOrders  = $monthlyTrend->pluck('total_orders')->values();

        // ── BOM / Material Context ────────────────────────────────────────
        $materials        = Material::all();
        $lowStockMaterials = $materials
            ->filter(fn($m) => $m->isLowStock())
            ->sortBy(fn($m) => (float) $m->available_stock)
            ->values();
        $lowStockCount    = $lowStockMaterials->count();

        $reservedStockTotal = (float) $materials->sum('reserved_stock');

        // 30-day consumption per material, used for the days-of-cover estimate.
        $deductionRates = DB::table('material_logs')
            ->where('type', 'auto_deduction')
            ->where('created_at', '>=', now()->subDays(30)->startOfDay())
            ->select('material_id', DB::raw('COALESCE(SUM(quantity_deducted), 0) as qty'))
            ->groupBy('material_id')
            ->get()
            ->keyBy('material_id');

        // Stock health: coverage relative to the restock trigger point.
        // The materials table has no max_stock column, so the bar is scaled against
        // low_stock_threshold — "how many times above the trigger am I?".
        $stockHealth = $materials->map(function ($m) use ($deductionRates) {
            $current   = (float) $m->current_stock;
            $reserved  = (float) $m->reserved_stock;
            // Sellable stock = physical minus reserved (reserved units are spoken for).
            $available = max(0, $current - $reserved);
            $threshold = (float) $m->low_stock_threshold;
            $pct       = $threshold > 0 ? min(100, (int) round(($available / $threshold) * 100)) : ($available > 0 ? 100 : 0);

            $used30 = 0.0;
            $rateRow = $deductionRates->get($m->id);
            if ($rateRow) {
                $used30 = (float) $rateRow->qty;
            }
            $daily  = $used30 / 30;
            $cover  = $daily > 0 ? (int) floor($available / $daily) : null;

            $tone = match (true) {
                $available <= 0                         => 'out',
                $threshold > 0 && $available <= $threshold => 'critical',
                $threshold > 0 && $available <= $threshold * 2 => 'watch',
                default                                  => 'healthy',
            };

            return (object) [
                'material'  => $m,
                'current'   => $current,
                'available' => $available,
                'threshold' => $threshold,
                'reserved'  => $reserved,
                'pct'       => $pct,
                'tone'      => $tone,
                'cover'     => $cover,
            ];
        })->sortBy('pct')->values();

        // ── Operations Module ─────────────────────────────────────────────
        $pipelineCounts = Order::whereNull('parent_id')
            ->select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');

        $designReviewQueue = Order::with('user')
            ->where('status', 'design_review')
            ->whereNull('parent_id')
            ->orderBy('created_at')
            ->take(10)
            ->get()
            ->map(function ($o) {
                $o->age_days = (int) round($o->created_at->diffInDays(now()));
                return $o;
            });

        $oldestPending = Order::where('status', 'pending')
            ->whereNull('parent_id')
            ->orderBy('created_at')
            ->first();
        $oldestPendingDays = $oldestPending ? (int) round($oldestPending->created_at->diffInDays(now())) : 0;

        // Pending-order aging buckets
        $agingBuckets = ['0–2 days' => 0, '3–7 days' => 0, '8–14 days' => 0, '15+ days' => 0];
        Order::where('status', 'pending')
            ->whereNull('parent_id')
            ->get()
            ->each(function ($o) use (&$agingBuckets) {
                $d = (int) round($o->created_at->diffInDays(now()));
                if ($d <= 2)      { $agingBuckets['0–2 days']++; }
                elseif ($d <= 7)  { $agingBuckets['3–7 days']++; }
                elseif ($d <= 14) { $agingBuckets['8–14 days']++; }
                else              { $agingBuckets['15+ days']++; }
            });

        $fulfillmentSplit = Order::whereNull('parent_id')
            ->select('fulfillment_type', 'is_walkin', DB::raw('COUNT(*) as c'), DB::raw('COALESCE(SUM(total_price), 0) as amount'))
            ->groupBy('fulfillment_type', 'is_walkin')
            ->get();

        $ticketStats = [
            'open'   => (int) DB::table('tickets')->whereIn('status', ['pending', 'open', 'in_progress'])->count(),
            'urgent' => (int) DB::table('tickets')->whereIn('status', ['pending', 'open', 'in_progress'])->whereIn('priority', ['high', 'urgent'])->count(),
        ];

        // ── Supplier Analytics Module ─────────────────────────────────────
        $supplierStats = DB::table('suppliers as s')
            ->leftJoin('purchases as p', 'p.supplier_id', '=', 's.id')
            ->whereNull('s.deleted_at')
            ->select(
                's.id', 's.name', 's.contact_person', 's.phone', 's.email',
                DB::raw('COUNT(p.id) as po_count'),
                DB::raw("COALESCE(SUM(CASE WHEN p.status <> 'cancelled' THEN p.total_amount ELSE 0 END), 0) as total_spend"),
                DB::raw("COALESCE(SUM(CASE WHEN p.status = 'pending' THEN p.total_amount ELSE 0 END), 0) as open_po_value"),
                DB::raw('MAX(p.created_at) as last_po_at')
            )
            ->groupBy('s.id', 's.name', 's.contact_person', 's.phone', 's.email')
            ->orderByDesc('total_spend')
            ->get();

        // Normalised unit price per material — the same material can be sold in
        // different supplier pack sizes, so raw unit_cost is not comparable.
        // Dividing by conversion_qty puts every offer on the same per-unit basis.
        $supplierMaterialRows = DB::table('supplier_materials as sm')
            ->join('suppliers as s', 's.id', '=', 'sm.supplier_id')
            ->leftJoin('materials as m', 'm.id', '=', 'sm.inventory_material_id')
            ->whereNull('s.deleted_at')
            ->select(
                'sm.id', 'sm.supplier_id', 'sm.inventory_material_id', 'sm.supplier_unit_name',
                'sm.conversion_qty', 'sm.unit_cost',
                's.name as supplier_name',
                'm.name as material_name', 'm.unit as material_unit'
            )
            ->get()
            ->map(function ($r) {
                $conv = (float) $r->conversion_qty;
                $r->conversion_qty = $conv;
                $r->base_cost       = (float) $r->unit_cost;
                $r->normalized_cost = $conv > 0 ? round((float) $r->unit_cost / $conv, 4) : (float) $r->unit_cost;
                return $r;
            });

        $supplierPriceComparison = $supplierMaterialRows
            ->filter(fn($r) => $r->material_name && $r->normalized_cost > 0)
            ->groupBy('material_name')
            ->map(function ($rows) {
                $rows    = $rows->sortBy('normalized_cost')->values();
                $cheapest = $rows->first();
                $highest  = (float) $rows->max('normalized_cost');

                return (object) [
                    'material'       => $cheapest->material_name,
                    'unit'           => $cheapest->material_unit,
                    'options'        => $rows,
                    'cheapest'       => $cheapest->supplier_name,
                    'cheapest_cost'  => (float) $cheapest->normalized_cost,
                    'highest_cost'   => $highest,
                    'savings_pct'    => $highest > 0 ? (int) round((1 - ((float) $cheapest->normalized_cost / $highest)) * 100) : 0,
                    'supplier_count' => $rows->count(),
                ];
            })
            ->sortByDesc('savings_pct')
            ->values();

        // Payables aging — purchase orders that still owe the supplier.
        // Only 'pending' POs count: a 'received' PO has already been fulfilled.
        $payablesAging = DB::table('purchases as p')
            ->join('suppliers as s', 's.id', '=', 'p.supplier_id')
            ->whereNull('s.deleted_at')
            ->where('p.status', 'pending')
            ->select('p.id', 'p.reference_no', 'p.total_amount', 'p.due_date', 'p.status', 'p.created_at', 's.name as supplier_name')
            ->orderByRaw('p.due_date IS NULL, p.due_date asc')
            ->get()
            ->map(function ($p) {
                $due  = $p->due_date ? Carbon::parse($p->due_date)->startOfDay() : null;
                $days = $due ? (int) round(now()->startOfDay()->diffInDays($due, true)) * ($due->lt(now()->startOfDay()) ? -1 : 1) : null;

                $p->days_to_due = $days;
                $p->bucket = $days === null
                    ? 'no_due_date'
                    : ($days < 0 ? 'overdue' : ($days <= 7 ? 'due_soon' : 'scheduled'));

                return $p;
            });

        $poPipeline = DB::table('purchases')
            ->select('status', DB::raw('COUNT(*) as c'), DB::raw('COALESCE(SUM(total_amount), 0) as amount'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $supplierPaidTotal = (float) DB::table('supplier_payments')->sum('amount_paid');
        $supplierOutstanding = (float) $payablesAging->sum('total_amount') - $supplierPaidTotal;

        // ── "Needs Action" queue (Overview module) ────────────────────────
        $actionItems = collect([
            (object) [
                'key'   => 'proofs',
                'label' => 'Payment proofs awaiting verification',
                'count' => $pendingVerificationCount,
                'meta'  => '₱' . number_format($pendingVerificationTotal, 2),
                'tone'  => 'magenta',
                'icon'  => 'fa-receipt',
                'url'   => route('super-admin.orders.index'),
            ],
            (object) [
                'key'   => 'pending',
                'label' => 'Orders awaiting processing',
                'count' => $pendingOrdersCount,
                'meta'  => $oldestPendingDays > 0 ? 'oldest ' . $oldestPendingDays . 'd' : 'fresh',
                'tone'  => 'amber',
                'icon'  => 'fa-clipboard-list',
                'url'   => route('super-admin.orders.index'),
            ],
            (object) [
                'key'   => 'design',
                'label' => 'Designs waiting for review',
                'count' => (int) ($pipelineCounts['design_review'] ?? 0),
                'meta'  => 'artwork approval',
                'tone'  => 'cyan',
                'icon'  => 'fa-pen-ruler',
                'url'   => route('super-admin.orders.index'),
            ],
            (object) [
                'key'   => 'stock',
                'label' => 'Materials low on stock',
                'count' => $lowStockCount,
                'meta'  => 'restock needed',
                'tone'  => 'red',
                'icon'  => 'fa-boxes-packing',
                'url'   => route('admin.materials.index', ['stock_status' => 'low_stock']),
            ],
            (object) [
                'key'   => 'tickets',
                'label' => 'Open support tickets',
                'count' => $ticketStats['open'],
                'meta'  => $ticketStats['urgent'] > 0 ? $ticketStats['urgent'] . ' urgent' : 'none urgent',
                'tone'  => 'violet',
                'icon'  => 'fa-headset',
                'url'   => route('admin.tickets.index'),
            ],
            (object) [
                'key'   => 'payables',
                'label' => 'Supplier payables due',
                'count' => (int) $payablesAging->where('bucket', 'overdue')->count(),
                'meta'  => 'overdue POs',
                'tone'  => 'amber',
                'icon'  => 'fa-truck-field',
                'url'   => route('admin.purchases.index'),
            ],
        ])->filter(fn($i) => $i->count > 0)->values();

        return view('super_admin.dashboard', compact(
            'totalRevenue',
            'scopedOrderCount',
            'monthlyRevenue',
            'revenueLast30Days',
            'ordersLast30Days',
            'averageOrderValue',
            'totalOrdersCount',
            'pendingOrdersCount',
            'completedOrdersCount',
            'inProgressOrdersCount',
            'cancelledOrdersCount',
            'totalCustomersCount',
            'totalAdminsCount',
            'totalCollected',
            'collectedThisMonth',
            'pendingDownpaymentBalance',
            'pendingVerificationTotal',
            'pendingVerificationCount',
            'pendingProofs',
            'downpaymentAging',
            'uncollectedTotal',
            'customOrdersCount',
            'regularOrdersCount',
            'customRevenue',
            'regularRevenue',
            'topByRevenue',
            'topByUnits',
            'categoryBreakdown',
            'chartTrendLabels',
            'chartTrendRevenue',
            'chartTrendOrders',
            'materials',
            'lowStockCount',
            'lowStockMaterials',
            'reservedStockTotal',
            'stockHealth',
            'pipelineCounts',
            'designReviewQueue',
            'oldestPendingDays',
            'agingBuckets',
            'fulfillmentSplit',
            'ticketStats',
            'supplierStats',
            'supplierPriceComparison',
            'payablesAging',
            'poPipeline',
            'supplierPaidTotal',
            'supplierOutstanding',
            'actionItems'
        ));
    }

    /**
     * AJAX endpoint — returns JSON chart datasets for a given period filter.
     * GET /super-admin/analytics/data?period=daily|weekly|monthly|yearly&range=...
     */
    public function analyticsData(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $range  = (string) $request->input('range', 'all');
        if (!array_key_exists($range, self::RANGE_LABELS)) {
            $range = 'all';
        }

        $trend = match ($period) {
            'daily'   => $this->buildDailyTrend(30),
            'weekly'  => $this->buildWeeklyTrend(12),
            'yearly'  => $this->buildYearlyTrend(5),
            default   => $this->buildMonthlyTrend(12),
        };

        // KPI values for the same range, so the strip and the chart stay in sync.
        $scoped = $this->applyRange($this->activeRevenueOrders(), $range);
        $revenue = (float) $scoped->sum('total_price');
        $orders  = (int) $scoped->count();

        $collected = (float) Payment::where('status', 'verified')->sum('amount');
        $pending   = (float) $this->pendingVerificationProofs()->sum('amount');
        $pendingN  = (int) $this->pendingVerificationProofs()->count();
        $downpay   = (float) Order::whereNotIn('status', ['cancelled'])
            ->whereNotIn('payment_status', ['paid', 'refunded', 'cancelled'])
            ->where('remaining_balance', '>', 0)
            ->where($this->leafScope())
            ->sum('remaining_balance');

        $openOrders = (int) Order::whereNull('parent_id')
            ->whereIn('status', ['pending', 'design_review', 'approved', 'printing', 'ready'])
            ->count();

        return response()->json([
            'labels'  => $trend->pluck('label')->values(),
            'revenue' => $trend->pluck('revenue')->values(),
            'orders'  => $trend->pluck('total_orders')->values(),
            'kpis'    => [
                'totalRevenue'     => $revenue,
                'scopedOrderCount' => $orders,
                'averageOrderValue'=> $orders > 0 ? $revenue / $orders : 0,
                'totalCollected'   => $collected,
                'collectedThisMonth'=> (float) Payment::where('status', 'verified')
                    ->where('created_at', '>=', now()->startOfMonth())->sum('amount'),
                'pendingVerificationTotal' => $pending,
                'pendingVerificationCount' => $pendingN,
                'pendingDownpaymentBalance' => $downpay,
                'uncollectedTotal' => $pending + $downpay,
                'totalOrdersCount' => (int) Order::whereNull('parent_id')->count(),
                'inProgressOrdersCount' => (int) Order::whereNull('parent_id')
                    ->whereIn('status', ['approved', 'design_review', 'printing', 'ready', 'shipped'])->count(),
                'pendingOrdersCount' => (int) Order::where('status', 'pending')->whereNull('parent_id')->count(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Private helpers — trend builders
    // ─────────────────────────────────────────────────────────────────────────

    private function buildDailyTrend(int $days)
    {
        $raw = $this->activeRevenueOrders()
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->select(
                DB::raw("DATE(created_at) as period_key"),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get()
            ->keyBy('period_key');

        $result = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $row  = $raw->get($date);
            $result->push([
                'label'        => now()->subDays($i)->format('M j'),
                'revenue'      => $row ? (float) $row->revenue : 0,
                'total_orders' => $row ? (int) $row->total_orders : 0,
            ]);
        }
        return $result;
    }

    private function buildWeeklyTrend(int $weeks)
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $expr = $isSqlite ? "strftime('%Y-%W', created_at)" : "DATE_FORMAT(created_at, '%x-%v')";

        $raw = $this->activeRevenueOrders()
            ->where('created_at', '>=', now()->subWeeks($weeks)->startOfWeek())
            ->select(
                DB::raw("{$expr} as period_key"),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get()
            ->keyBy('period_key');

        $result = collect();
        for ($i = $weeks - 1; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $key       = $isSqlite ? $weekStart->format('Y-W') : $weekStart->format('o-W');
            $row       = $raw->get($key);
            $result->push([
                'label'        => 'Wk ' . $weekStart->format('M j'),
                'revenue'      => $row ? (float) $row->revenue : 0,
                'total_orders' => $row ? (int) $row->total_orders : 0,
            ]);
        }
        return $result;
    }

    private function buildMonthlyTrend(int $months = 12)
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $expr = $isSqlite ? "strftime('%Y-%m', created_at)" : "DATE_FORMAT(created_at, '%Y-%m')";

        // Ensure at least 6 months window so the line/bar chart forms a proportional progression curve
        $monthsToDisplay = max(6, $months);
        $windowStart = now()->subMonths($monthsToDisplay - 1)->startOfMonth();

        $raw = $this->activeRevenueOrders()
            ->where('created_at', '>=', $windowStart)
            ->select(
                DB::raw("{$expr} as period_key"),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get()
            ->keyBy('period_key');

        $result = collect();
        $cursor = $windowStart->copy();
        while ($cursor->lte(now())) {
            $month = $cursor->copy();
            $key   = $month->format('Y-m');
            $row   = $raw->get($key);
            $result->push([
                'label'        => $month->format('M Y'),
                'revenue'      => $row ? (float) $row->revenue : 0,
                'total_orders' => $row ? (int) $row->total_orders : 0,
            ]);
            $cursor->addMonthNoOverflow();
        }
        return $result;
    }

    private function buildYearlyTrend(int $years)
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $expr = $isSqlite ? "cast(strftime('%Y', created_at) as integer)" : "YEAR(created_at)";

        $raw = $this->activeRevenueOrders()
            ->where('created_at', '>=', now()->subYears($years - 1)->startOfYear())
            ->select(
                DB::raw("{$expr} as period_key"),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy('period_key')
            ->orderBy('period_key')
            ->get()
            ->keyBy('period_key');

        $result = collect();
        for ($i = $years - 1; $i >= 0; $i--) {
            $year = now()->subYears($i)->year;
            $row  = $raw->get($year);
            $result->push([
                'label'        => (string) $year,
                'revenue'      => $row ? (float) $row->revenue : 0,
                'total_orders' => $row ? (int) $row->total_orders : 0,
            ]);
        }
        return $result;
    }
}
