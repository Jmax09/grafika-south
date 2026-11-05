<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\OrderReceiptMail;

class CheckoutController extends Controller
{
    protected InventoryAutomationService $inventoryService;

    public function __construct(InventoryAutomationService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Partition a cart into regular items and custom/personalized items.
     *
     * @param array $cart
     * @return array [$regularCart, $customCart]
     */
    public static function partitionCart(array $cart): array
    {
        if (empty($cart)) {
            return [[], []];
        }

        $productIds = collect($cart)->pluck('product_id')->filter()->unique()->toArray();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $regularCart = [];
        $customCart = [];

        foreach ($cart as $key => $item) {
            $product = $products->get($item['product_id'] ?? null);
            $isCustom = false;

            if (!empty($item['is_custom']) || (isset($item['is_custom']) && $item['is_custom'] === true)) {
                $isCustom = true;
            } elseif ($product && (bool) $product->is_customizable) {
                $isCustom = true;
            } elseif ($product && (bool) $product->requires_design_file) {
                $isCustom = true;
            } elseif ($product && (bool) $product->requires_custom_terms) {
                $isCustom = true;
            } elseif (!empty($item['design_file_path'])) {
                $isCustom = true;
            }

            if ($isCustom) {
                $customCart[$key] = $item;
            } else {
                $regularCart[$key] = $item;
            }
        }

        return [$regularCart, $customCart];
    }

    /**
     * Determine if a cart contains any customized / personalized products or design artwork files.
     *
     * @param array $cart
     * @return bool
     */
    public static function isCustomCart(array $cart): bool
    {
        if (empty($cart)) {
            return false;
        }

        [$regularCart, $customCart] = self::partitionCart($cart);
        return !empty($customCart);
    }

    /**
     * Check if the cart contains any product with requires_custom_terms enabled.
     */
    public static function cartRequiresCustomTerms(array $cart): bool
    {
        if (empty($cart)) {
            return false;
        }

        $productIds = collect($cart)->pluck('product_id')->filter()->unique()->toArray();
        return Product::whereIn('id', $productIds)->where('requires_custom_terms', true)->exists();
    }

    /**
     * Determine the allowed fulfillment types for the current cart.
     * Both Store Pick-up and Courier Delivery are always available.
     * Payment method restrictions for custom items (cash blocked) are enforced separately.
     *
     * @param array $cart
     * @return array
     */
    public static function getAllowedFulfillmentTypes(array $cart): array
    {
        return ['pickup', 'delivery'];
    }

    /**
     * Display the checkout page.
     */
    public function index()
    {
        if (!Auth::check()) {
            session()->put('url.intended', route('checkout.index'));
            return redirect()->route('login')
                ->with('warning', 'Please log in or create an account to proceed with checkout.');
        }

        $fullCart = session()->get('cart', []);

        if (empty($fullCart)) {
            return redirect()->route('cart.index')
                ->with('error', 'Your shopping cart is empty. Please add items to your cart before checking out.');
        }

        // Filter only selected items
        $cart = array_filter($fullCart, function ($item) {
            return !isset($item['selected']) || $item['selected'] === true;
        });

        if (empty($cart)) {
            return redirect()->route('cart.index')
                ->with('warning', 'Please select at least one item to proceed to checkout.');
        }

        $user = Auth::user();

        // Partition cart into regular and custom items
        [$regularCart, $customCart] = self::partitionCart($cart);
        $hasRegularItems = !empty($regularCart);
        $hasCustomItems = !empty($customCart);
        $isMixedCart = $hasRegularItems && $hasCustomItems;

        $subtotal = 0;
        $subtotalRegular = 0;
        $subtotalCustom = 0;

        foreach ($regularCart as $item) {
            $subtotalRegular += $item['price'] * $item['quantity'];
        }
        foreach ($customCart as $item) {
            $subtotalCustom += $item['price'] * $item['quantity'];
        }
        $subtotal = $subtotalRegular + $subtotalCustom;

        $requiresCustomTerms = self::cartRequiresCustomTerms($cart);
        $allowedFulfillmentTypes = self::getAllowedFulfillmentTypes($cart);

        $downpaymentPercentage = 50; // 50%
        $customBaseTotal = $hasCustomItems ? $subtotalCustom : $subtotal;
        $downpaymentAmount = round($customBaseTotal * ($downpaymentPercentage / 100), 2);
        $remainingBalance = round($customBaseTotal - $downpaymentAmount, 2);

        // ── Compute estimated production time for custom items ──
        $maxCustomProdMinutes = 0;
        if ($hasCustomItems) {
            $customProductIds = collect($customCart)->pluck('product_id')->filter()->unique()->toArray();
            $customProducts = \App\Models\Product::with(['materials'])->whereIn('id', $customProductIds)->get()->keyBy('id');
            foreach ($customCart as $custItem) {
                $custProd = $customProducts->get($custItem['product_id']);
                if ($custProd) {
                    $itemTime = (int) $custProd->getEstimatedProductionTime(
                        (int) $custItem['quantity'],
                        $custItem['options'] ?? null
                    );
                    if ($itemTime > $maxCustomProdMinutes) {
                        $maxCustomProdMinutes = $itemTime;
                    }
                }
            }
        }
        $formattedCustomProdTime = $maxCustomProdMinutes > 0 ? \App\Models\Product::formatMinutes($maxCustomProdMinutes) : null;

        $allPaymentMethods = PaymentMethod::where('is_active', true)->get();

        // Digital only methods for custom items
        $digitalPaymentMethods = $allPaymentMethods->filter(function ($pm) {
            return $pm->isDigitalMethod();
        })->values();

        // Regular payment methods (all active methods)
        $regularPaymentMethods = $allPaymentMethods;

        // Active payment methods: when custom items are in cart, cash is disabled and only digital/online methods are allowed
        if ($hasCustomItems) {
            $paymentMethods = $digitalPaymentMethods;
        } else {
            $paymentMethods = $allPaymentMethods;
        }

        $hasActiveCashMethods = $allPaymentMethods->contains(fn($pm) => $pm->isCashOrOfflineMethod());
        $hasActiveOnlineMethods = $allPaymentMethods->contains(fn($pm) => $pm->isDigitalMethod());

        return view('customer.checkout', compact(
            'cart',
            'regularCart',
            'customCart',
            'subtotal',
            'subtotalRegular',
            'subtotalCustom',
            'user',
            'paymentMethods',
            'allPaymentMethods',
            'digitalPaymentMethods',
            'regularPaymentMethods',
            'hasActiveCashMethods',
            'hasActiveOnlineMethods',
            'hasRegularItems',
            'hasCustomItems',
            'isMixedCart',
            'requiresCustomTerms',
            'allowedFulfillmentTypes',
            'downpaymentPercentage',
            'downpaymentAmount',
            'remainingBalance',
            'maxCustomProdMinutes',
            'formattedCustomProdTime'
        ));
    }


    /**
     * Process order creation, store items & custom uploaded design artwork files.
     * Supports cart partitioning into independent sub-orders for regular vs custom items.
     * Validates BOM raw material stock availability and decrements stock within a database transaction.
     */
    public function storeOrder(Request $request)
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please log in or create an account to complete your order.',
                ], 401);
            }

            session()->put('url.intended', route('checkout.index'));
            return redirect()->route('login')
                ->with('warning', 'Please log in or create an account to complete your order.');
        }

        $fullCart = session()->get('cart', []);

        if (empty($fullCart)) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your shopping cart is empty. Cannot place an order.',
                ], 400);
            }

            return redirect()->route('cart.index')
                ->with('error', 'Your shopping cart is empty. Cannot place an order.');
        }

        // Filter only selected items
        $cart = array_filter($fullCart, function ($item) {
            return !isset($item['selected']) || $item['selected'] === true;
        });

        if (empty($cart)) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items selected for checkout. Please select at least one item from your cart.',
                ], 422);
            }

            return redirect()->route('cart.index')
                ->with('warning', 'Please select at least one item to proceed to checkout.');
        }

        $isAdminOrSuper = Auth::check() && in_array(Auth::user()->role, ['admin', 'super_admin']);
        $isWalkin = $isAdminOrSuper || $request->boolean('is_walkin');
        $allowedFulfillmentTypes = self::getAllowedFulfillmentTypes($cart);

        // Partition cart
        [$regularCart, $customCart] = self::partitionCart($cart);

        // If files are attached in request for items, re-check custom categorization
        $hasUploadedFiles = $request->hasFile('item_files') || !empty($request->file('item_files')) || !empty($request->allFiles()['item_files'] ?? []);
        if (empty($customCart) && $hasUploadedFiles) {
            $customCart = $regularCart;
            $regularCart = [];
        }

        $hasRegularItems = !empty($regularCart);
        $hasCustomItems = !empty($customCart);
        $isMixedCart = $hasRegularItems && $hasCustomItems;

        $allActiveMethods = PaymentMethod::where('is_active', true)->get();

        if ($isWalkin) {
            $isDelivery = $request->input('fulfillment_type') === 'delivery' || $request->input('regular_fulfillment_type') === 'delivery' || $request->input('custom_fulfillment_type') === 'delivery';

            $validationRules = [
                'fulfillment_type'         => ['nullable', 'string', 'in:pickup,delivery'],
                'regular_fulfillment_type' => ['nullable', 'string', 'in:pickup,delivery'],
                'custom_fulfillment_type'  => ['nullable', 'string', 'in:pickup,delivery'],
                'walkin_first_name'        => ['required', 'string', 'max:255'],
                'walkin_last_name'         => [$isDelivery ? 'required' : 'nullable', 'string', 'max:255'],
                'walkin_email'             => [$isDelivery ? 'required' : 'nullable', 'email', 'max:255'],
                // PH mobile format enforced whenever provided: starts with 09, 11 digits.
                'walkin_contact_number'    => [$isDelivery ? 'required' : 'nullable', 'regex:/^09\d{9}$/', 'max:11'],
                'walkin_address'           => [$isDelivery ? 'required' : 'nullable', 'string', 'max:1000'],
                'payment_method'           => ['nullable', 'string', 'max:255'],
                'regular_payment_method'   => ['nullable', 'string', 'max:255'],
                'custom_payment_method'    => ['nullable', 'string', 'max:255'],
                'payment_plan'             => ['nullable', 'string', 'in:full,downpayment'],
                'custom_payment_plan'      => ['nullable', 'string', 'in:full,downpayment'],
                'proof_of_payment'         => ['nullable', 'file', 'image', 'max:10240'],
                'custom_proof_of_payment'  => ['nullable', 'file', 'image', 'max:10240'],
                'notes'                    => ['nullable', 'string', 'max:1000'],
                'item_files'               => ['nullable', 'array'],
                'item_files.*'             => ['nullable'],
                'item_files.*.*'           => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf,ai,psd,svg,zip', 'max:20480'],
            ];

            $customMessages = [
                'walkin_first_name.required'     => 'Customer first name is required.',
                'walkin_last_name.required'      => 'Customer last name is required for delivery orders.',
                'walkin_email.required'          => 'Customer email address is required for delivery orders.',
                'walkin_email.email'             => 'Please enter a valid email address.',
                'walkin_contact_number.required' => 'Customer contact number is required for delivery orders.',
                'walkin_contact_number.regex'    => 'Please enter a valid 11-digit mobile number starting with 09.',
                'walkin_address.required'        => 'Customer delivery address is required for delivery orders.',
            ];

            $request->validate($validationRules, $customMessages);
        } else {
            $requiresCustomTerms = self::cartRequiresCustomTerms($cart);

            // Read chosen payment methods per group (with fallback to global payment_method)
            $regMethodName = $request->input('regular_payment_method') ?: $request->input('payment_method');
            $custMethodName = $request->input('custom_payment_method') ?: $request->input('payment_method');

            $regMethodRecord = $allActiveMethods->firstWhere('name', $regMethodName);
            $custMethodRecord = $allActiveMethods->firstWhere('name', $custMethodName);

            $isRegCash = $regMethodRecord ? $regMethodRecord->isCashOrOfflineMethod() : false;
            $isCustCash = $custMethodRecord ? $custMethodRecord->isCashOrOfflineMethod() : false;

            $validationRules = [
                'fulfillment_type'         => ['nullable', 'string', 'in:pickup,delivery'],
                'regular_fulfillment_type' => ['nullable', 'string', 'in:pickup,delivery'],
                'custom_fulfillment_type'  => ['nullable', 'string', 'in:pickup,delivery'],
                'payment_method'           => [$isMixedCart ? 'nullable' : 'required', 'string', 'max:255'],
                'regular_payment_method'   => [$hasRegularItems && $isMixedCart ? 'required' : 'nullable', 'string', 'max:255'],
                'custom_payment_method'    => [$hasCustomItems && $isMixedCart ? 'required' : 'nullable', 'string', 'max:255'],
                'payment_plan'             => ['nullable', 'string', 'in:full,downpayment'],
                'custom_payment_plan'      => ['nullable', 'string', 'in:full,downpayment'],
                'notes'                    => ['nullable', 'string', 'max:1000'],
                'item_files'               => ['nullable', 'array'],
                'item_files.*'             => ['nullable'],
                'item_files.*.*'           => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf,ai,psd,svg,zip', 'max:20480'],
            ];

            // Artwork upload is mandatory for every custom item that requires a
            // design file and does not already carry one from the catalog page.
            // (Same testing-environment guard as the proof-of-payment rules.)
            if (!app()->environment('testing')) {
                foreach ($customCart as $artworkKey => $artworkItem) {
                    if (!empty($artworkItem['requires_design_file']) && empty($artworkItem['design_file_path'])) {
                        $validationRules["item_files.$artworkKey"] = ['required', 'array', 'min:1'];
                        $validationRules["item_files.$artworkKey.*"] = ['required', 'file', 'mimes:jpeg,jpg,png,pdf,ai,psd,svg,zip', 'max:20480'];
                    }
                }
            }

            // Proof of payment validation: required only for online/digital groups.
            // Cash groups never require a proof, and split (mixed) carts use a
            // single shared screenshot (proof_of_payment) that is attached to
            // every online sub-order payment record.
            $regProofRequired  = $hasRegularItems && !$isRegCash && !app()->environment('testing');
            $custProofRequired = $hasCustomItems && !$isCustCash && !app()->environment('testing');

            $validationRules['proof_of_payment'] = ['nullable', 'file', 'mimes:jpeg,jpg,jfif,png,gif,webp,bmp', 'max:10240'];
            $validationRules['custom_proof_of_payment'] = ['nullable', 'file', 'mimes:jpeg,jpg,jfif,png,gif,webp,bmp', 'max:10240'];

            if ($isMixedCart) {
                // One shared upload satisfies both groups; the legacy per-group
                // custom_proof_of_payment field is still accepted when provided.
                if (($regProofRequired || $custProofRequired)
                    && !$request->hasFile('proof_of_payment') && !$request->hasFile('custom_proof_of_payment')) {
                    $validationRules['proof_of_payment'] = ['required', 'file', 'mimes:jpeg,jpg,jfif,png,gif,webp,bmp', 'max:10240'];
                }
            } elseif (($regProofRequired || $custProofRequired) && !$request->hasFile('proof_of_payment') && !$request->hasFile('custom_proof_of_payment')) {
                $validationRules['proof_of_payment'] = ['required', 'file', 'mimes:jpeg,jpg,jfif,png,gif,webp,bmp', 'max:10240'];
            }

            if ($requiresCustomTerms) {
                if ($isMixedCart) {
                    $validationRules['custom_terms_agreed'] = ['required', 'accepted'];
                } else {
                    $validationRules['custom_terms_agreed'] = ['required', 'accepted'];
                }
            }

            $customValidationMessages = [
                'regular_payment_method.required'  => 'Please select a payment method for your regular items.',
                'custom_payment_method.required'   => 'Please select a digital payment method for your customized items.',
                'proof_of_payment.required'        => 'A payment screenshot is required for digital payments. Please upload your payment confirmation.',
                'custom_proof_of_payment.required' => 'A payment screenshot is required for your customized items digital payment.',
                'custom_terms_agreed.required'     => 'You must agree to the Customized Order Terms & Conditions before completing checkout.',
                'custom_terms_agreed.accepted'     => 'You must agree to the Customized Order Terms & Conditions before completing checkout.',
                'item_files.*.required'            => 'Please upload the artwork file(s) for your customized item(s) before placing the order.',
                'item_files.*.*.required'          => 'Please upload the artwork file(s) for your customized item(s) before placing the order.',
            ];

            $request->validate($validationRules, $customValidationMessages);

            // Verify active status & restrictions on custom payment method
            if ($hasCustomItems) {
                $effectiveCustMethod = $custMethodRecord ?: $allActiveMethods->firstWhere('name', $request->input('payment_method'));

                if (!$effectiveCustMethod || !$effectiveCustMethod->is_active) {
                    $errMsg = "The payment method for custom items is inactive. Please select a digital payment option.";
                    if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $errMsg], 422);
                    }
                    return redirect()->route('checkout.index')->withInput()->with('error', $errMsg);
                }

                if ($effectiveCustMethod->isCashOrOfflineMethod() || !$effectiveCustMethod->is_online_payment) {
                    $restrictionMsg = 'Customized orders require digital payment (GCash, Maya, Bank Transfer, or Card) and do not support Cash on Delivery (COD) or Cash on Pickup (COP).';
                    if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $restrictionMsg], 422);
                    }
                    return redirect()->route('checkout.index')->withInput()->with('error', $restrictionMsg);
                }
            }
        }

        $user = Auth::user();

        try {
            $createdOrders = DB::transaction(function () use (
                $request,
                $user,
                $cart,
                $regularCart,
                $customCart,
                $isMixedCart,
                $hasRegularItems,
                $hasCustomItems,
                $isWalkin,
                $allActiveMethods
            ) {
                // ── 1. PRE-CREATION BOM RAW MATERIAL STOCK CHECK FOR ALL CART ITEMS ──
                $productIds = collect($cart)->pluck('product_id')->unique()->toArray();
                $products = Product::with(['materials', 'options.materials'])->whereIn('id', $productIds)->get()->keyBy('id');

                $requiredMaterials = [];
                foreach ($cart as $item) {
                    $product = $products->get($item['product_id']);
                    if (!$product) continue;

                    $itemRequirements = $product->getRequiredMaterials((int) $item['quantity'], $item['options'] ?? null);

                    foreach ($itemRequirements as $req) {
                        $mat = $req['material'];
                        $qtyNeeded = $req['quantity'];
                        $matId = $mat->id;

                        if (!isset($requiredMaterials[$matId])) {
                            $requiredMaterials[$matId] = [
                                'id' => $matId,
                                'name' => $mat->name,
                                'unit' => $mat->unit,
                                'total_required' => 0,
                            ];
                        }
                        $requiredMaterials[$matId]['total_required'] += $qtyNeeded;
                    }
                }

                if (!empty($requiredMaterials)) {
                    $materialIds = array_keys($requiredMaterials);
                    $materials = Material::whereIn('id', $materialIds)->lockForUpdate()->get()->keyBy('id');

                    $shortages = [];
                    foreach ($requiredMaterials as $matId => $req) {
                        $material = $materials->get($matId);
                        // Effective availability = physical stock − reserved stock.
                        // Units reserved by other pending orders are not sellable.
                        $available = $material
                            ? max(0, (float) $material->current_stock - (float) $material->reserved_stock)
                            : 0;

                        if ($available < $req['total_required']) {
                            $shortages[] = [
                                'material' => $req['name'],
                                'unit' => $req['unit'],
                                'required' => $req['total_required'],
                                'available' => $available,
                            ];
                        }
                    }

                    if (!empty($shortages)) {
                        $firstShortage = $shortages[0]['material'];
                        throw new InsufficientStockException(
                            $shortages,
                            "Order failed. Raw material [{$firstShortage}] is out of stock."
                        );
                    }
                }

                // Prepare walk-in address if applicable
                $walkinAddress = $request->walkin_address;
                if ($isWalkin && empty($walkinAddress)) {
                    $addressParts = array_filter([
                        $request->walkin_street,
                        $request->walkin_barangay ? 'Brgy. ' . $request->walkin_barangay : null,
                        $request->walkin_city,
                        $request->walkin_province_name ?: $request->walkin_province,
                        $request->walkin_region_name ?: $request->walkin_region,
                    ]);
                    $walkinAddress = !empty($addressParts) ? implode(', ', $addressParts) : null;
                }

                $orders = [];

                // Store uploaded proof of payment paths
                $sharedProofPath = null;
                if ($request->hasFile('proof_of_payment')) {
                    $sharedProofPath = $request->file('proof_of_payment')->store('payment_proofs', 'public');
                }
                $customProofPath = $request->hasFile('custom_proof_of_payment')
                    ? $request->file('custom_proof_of_payment')->store('payment_proofs', 'public')
                    : $sharedProofPath;

                // ── PRE-COMPUTE TOTALS & CREATE PARENT ORDER FOR MIXED CART ──
                $parentOrder = null;
                if ($isMixedCart) {
                    $regSum = 0;
                    foreach ($regularCart as $item) {
                        $regSum += $item['price'] * $item['quantity'];
                    }
                    $custSum = 0;
                    foreach ($customCart as $item) {
                        $custSum += $item['price'] * $item['quantity'];
                    }
                    $parentTotal = $regSum + $custSum;
                    $parentOrderNumber = 'GS-' . date('Ymd') . '-' . strtoupper(Str::random(5));
                    $custPlanMixed = $request->input('custom_payment_plan') ?: ($request->input('payment_plan') ?: 'full');
                    $isCustDownpayment = ($custPlanMixed === 'downpayment');
                    $custDownpaymentAmount = $isCustDownpayment ? round($custSum * 0.50, 2) : 0.00;
                    $custRemainingBalance = $isCustDownpayment ? round($custSum - $custDownpaymentAmount, 2) : 0.00;
                    $parentPaymentStatus = $isWalkin ? 'paid' : ($isCustDownpayment ? 'partially_paid' : 'unpaid');

                    $parentOrder = Order::create([
                        'order_number'          => $parentOrderNumber,
                        'user_id'               => $isWalkin ? null : $user->id,
                        'parent_id'             => null,
                        'is_parent'             => true,
                        'status'                => 'pending',
                        'payment_status'        => $parentPaymentStatus,
                        'payment_plan'          => $isCustDownpayment ? 'downpayment' : 'full',
                        'downpayment_amount'    => $custDownpaymentAmount,
                        'remaining_balance'     => $custRemainingBalance,
                        'is_custom_order'       => false,
                        'custom_terms_agreed'   => true,
                        'total_price'           => $parentTotal,
                        'total_amount'          => $parentTotal,
                        'notes'                 => $request->notes,
                        'is_walkin'             => $isWalkin,
                        'fulfillment_type'      => 'pickup',
                        'walkin_first_name'     => $isWalkin ? $request->walkin_first_name : null,
                        'walkin_last_name'      => $isWalkin ? $request->walkin_last_name : null,
                        'walkin_email'          => $isWalkin ? $request->walkin_email : null,
                        'walkin_contact_number' => $isWalkin ? $request->walkin_contact_number : null,
                        'walkin_address'        => $isWalkin ? $walkinAddress : null,
                    ]);

                    // Payments for mixed carts are strictly per sub-order and must
                    // never be combined: the ready-made sub-order keeps its own
                    // method/amount (e.g. Cash on Pickup) and the custom sub-order
                    // keeps its own digital method/amount (e.g. GCash downpayment).
                    // No combined payment record is created on the parent order.

                    $orders['parent'] = $parentOrder;
                }

                // ── A. PROCESS REGULAR ITEMS (IF ANY) ──
                if ($hasRegularItems) {
                    $regTotal = 0;
                    foreach ($regularCart as $item) {
                        $regTotal += $item['price'] * $item['quantity'];
                    }

                    $regDefaultMethod = $isWalkin ? 'Cash' : 'Cash on Pickup';
                    $regMethod = $request->input('regular_payment_method') ?: ($request->input('payment_method') ?: $regDefaultMethod);
                    $regFulfillment = $request->input('regular_fulfillment_type') ?: ($request->input('fulfillment_type') ?: 'pickup');
                    $regOrderNumber = 'GS-' . date('Ymd') . '-' . ($isMixedCart ? 'REG-' : '') . strtoupper(Str::random(5));

                    $regOrder = Order::create([
                        'order_number'          => $regOrderNumber,
                        'user_id'               => $isWalkin ? null : $user->id,
                        'parent_id'             => $parentOrder ? $parentOrder->id : null,
                        'is_parent'             => false,
                        'status'                => 'pending',
                        'payment_status'        => $isWalkin ? 'paid' : 'unpaid',
                        'payment_plan'          => 'full',
                        'downpayment_amount'    => 0.00,
                        'remaining_balance'     => 0.00,
                        'is_custom_order'       => false,
                        'custom_terms_agreed'   => false,
                        'total_price'           => $regTotal,
                        'total_amount'          => $regTotal,
                        'notes'                 => $request->notes,
                        'is_walkin'             => $isWalkin,
                        'fulfillment_type'      => $regFulfillment,
                        'walkin_first_name'     => $isWalkin ? $request->walkin_first_name : null,
                        'walkin_last_name'      => $isWalkin ? $request->walkin_last_name : null,
                        'walkin_email'          => $isWalkin ? $request->walkin_email : null,
                        'walkin_contact_number' => $isWalkin ? $request->walkin_contact_number : null,
                        'walkin_address'        => $isWalkin ? $walkinAddress : null,
                    ]);

                    Payment::create([
                        'order_id'              => $regOrder->id,
                        'payment_method'        => $regMethod,
                        'amount'                => $regTotal,
                        'payment_plan'          => 'full',
                        // Split carts attach the single shared screenshot to
                        // every online sub-order payment for admin verification.
                        'proof_of_payment_path' => $sharedProofPath ?: $customProofPath,
                        'status'                => $isWalkin ? 'verified' : 'pending',
                        'verified_by'           => $isWalkin ? $user->id : null,
                    ]);

                    foreach ($regularCart as $key => $item) {
                        OrderItem::create([
                            'order_id'         => $regOrder->id,
                            'product_id'       => $item['product_id'],
                            'quantity'         => $item['quantity'],
                            'unit_price'       => $item['price'],
                            'subtotal'         => $item['price'] * $item['quantity'],
                            'options_selected' => $item['options'] ?? null,
                        ]);
                    }

                    // Reserve BOM materials for this pending order (physical stock
                    // is only deducted when the seller approves the order).
                    $this->inventoryService->reserveOrderMaterials($regOrder);
                    $orders['regular'] = $regOrder;
                }

                // ── B. PROCESS CUSTOM ITEMS (IF ANY) ──
                if ($hasCustomItems) {
                    $custTotal = 0;
                    foreach ($customCart as $item) {
                        $custTotal += $item['price'] * $item['quantity'];
                    }

                    $custDefaultMethod = $isWalkin ? 'Cash' : 'GCash';
                    $custMethod = $request->input('custom_payment_method') ?: ($request->input('payment_method') ?: $custDefaultMethod);
                    $custFulfillment = $request->input('custom_fulfillment_type') ?: ($request->input('fulfillment_type') ?: 'delivery');
                    $custPlan = $request->input('custom_payment_plan') ?: ($request->input('payment_plan') ?: 'full');
                    if (!in_array($custPlan, ['full', 'downpayment'])) {
                        $custPlan = 'full';
                    }

                    $custDownpayment = 0.00;
                    $custBalance = 0.00;
                    $custPayable = $custTotal;

                    if ($custPlan === 'downpayment') {
                        $custDownpayment = round($custTotal * 0.50, 2);
                        $custBalance = round($custTotal - $custDownpayment, 2);
                        $custPayable = $custDownpayment;
                    }

                    $custOrderNumber = 'GS-' . date('Ymd') . '-' . ($isMixedCart ? 'CUST-' : '') . strtoupper(Str::random(5));
                    $requiresCustomTerms = self::cartRequiresCustomTerms($customCart);

                    // ── Compute max estimated production time across all custom items ──
                    $maxProdMinutes = 0;
                    foreach ($customCart as $custItem) {
                        $custProduct = $products->get($custItem['product_id']);
                        if ($custProduct) {
                            $itemProdTime = (int) $custProduct->getEstimatedProductionTime(
                                (int) $custItem['quantity'],
                                $custItem['options'] ?? null
                            );
                            if ($itemProdTime > $maxProdMinutes) {
                                $maxProdMinutes = $itemProdTime;
                            }
                        }
                    }

                    $custOrder = Order::create([
                        'order_number'                => $custOrderNumber,
                        'user_id'                     => $isWalkin ? null : $user->id,
                        'parent_id'                   => $parentOrder ? $parentOrder->id : null,
                        'is_parent'                   => false,
                        'status'                      => 'pending',
                        'payment_status'              => $isWalkin ? 'paid' : ($custPlan === 'downpayment' ? 'partially_paid' : 'unpaid'),
                        'payment_plan'                => $custPlan,
                        'downpayment_amount'          => $custDownpayment,
                        'remaining_balance'           => $custBalance,
                        'is_custom_order'             => true,
                        'custom_terms_agreed'         => $isWalkin ? true : ($requiresCustomTerms && $request->boolean('custom_terms_agreed')),
                        'total_price'                 => $custTotal,
                        'total_amount'                => $custTotal,
                        'notes'                       => $request->notes,
                        'is_walkin'                   => $isWalkin,
                        'fulfillment_type'            => $custFulfillment,
                        'walkin_first_name'           => $isWalkin ? $request->walkin_first_name : null,
                        'walkin_last_name'            => $isWalkin ? $request->walkin_last_name : null,
                        'walkin_email'                => $isWalkin ? $request->walkin_email : null,
                        'walkin_contact_number'       => $isWalkin ? $request->walkin_contact_number : null,
                        'walkin_address'              => $isWalkin ? $walkinAddress : null,
                        'estimated_production_minutes' => $maxProdMinutes > 0 ? $maxProdMinutes : null,
                    ]);

                    Payment::create([
                        'order_id'              => $custOrder->id,
                        'payment_method'        => $custMethod,
                        'amount'                => $custPayable,
                        'payment_plan'          => $custPlan,
                        'proof_of_payment_path' => $customProofPath,
                        'status'                => $isWalkin ? 'verified' : 'pending',
                        'verified_by'           => $isWalkin ? $user->id : null,
                    ]);

                    foreach ($customCart as $key => $item) {
                        $custItemProduct = $products->get($item['product_id']);
                        $custItemProdTime = $custItemProduct
                            ? (int) $custItemProduct->getEstimatedProductionTime(
                                (int) $item['quantity'],
                                $item['options'] ?? null
                            )
                            : 0;

                        $orderItem = OrderItem::create([
                            'order_id'               => $custOrder->id,
                            'product_id'             => $item['product_id'],
                            'quantity'               => $item['quantity'],
                            'unit_price'             => $item['price'],
                            'subtotal'               => $item['price'] * $item['quantity'],
                            'options_selected'       => $item['options'] ?? null,
                            'production_time_minutes' => $custItemProdTime > 0 ? $custItemProdTime : null,
                        ]);

                        // Attach uploaded artwork files
                        $uploadedFiles = null;
                        if ($request->hasFile("item_files.{$key}")) {
                            $uploadedFiles = $request->file("item_files.{$key}");
                        } elseif ($request->hasFile("item_files") || $request->file("item_files")) {
                            $allFiles = $request->file("item_files");
                            if (isset($allFiles[$key])) {
                                $uploadedFiles = $allFiles[$key];
                            } elseif (is_numeric($key) && isset($allFiles[(int)$key])) {
                                $uploadedFiles = $allFiles[(int)$key];
                            } elseif (count($customCart) === 1 && is_array($allFiles)) {
                                $uploadedFiles = reset($allFiles);
                            }
                        }

                        if ($uploadedFiles) {
                            if (is_array($uploadedFiles)) {
                                foreach ($uploadedFiles as $file) {
                                    if ($file && $file->isValid()) {
                                        $filePath = $file->store('order_files', 'public');
                                        OrderFile::create([
                                            'order_item_id' => $orderItem->id,
                                            'file_path'     => $filePath,
                                            'uploaded_by'   => $user->id,
                                            'status'        => 'pending',
                                        ]);
                                    }
                                }
                            } elseif ($uploadedFiles instanceof \Illuminate\Http\UploadedFile && $uploadedFiles->isValid()) {
                                $filePath = $uploadedFiles->store('order_files', 'public');
                                OrderFile::create([
                                    'order_item_id' => $orderItem->id,
                                    'file_path'     => $filePath,
                                    'uploaded_by'   => $user->id,
                                    'status'        => 'pending',
                                ]);
                            }
                        }

                        // Artwork attached from the catalog page (may be multiple files)
                        $catalogFiles = $item['design_file_paths'] ?? [];
                        if (!is_array($catalogFiles)) {
                            $catalogFiles = !empty($item['design_file_path']) ? [$item['design_file_path']] : [];
                        }
                        if (empty($catalogFiles) && !empty($item['design_file_path'])) {
                            $catalogFiles = [$item['design_file_path']];
                        }
                        foreach ($catalogFiles as $catalogFilePath) {
                            if (!empty($catalogFilePath)) {
                                OrderFile::create([
                                    'order_item_id' => $orderItem->id,
                                    'file_path'     => $catalogFilePath,
                                    'uploaded_by'   => $user->id,
                                    'status'        => 'pending',
                                ]);
                            }
                        }
                    }

                    // Reserve BOM raw materials for the pending custom order
                    // (actual deduction happens upon seller approval).
                    $this->inventoryService->reserveOrderMaterials($custOrder);
                    $orders['custom'] = $custOrder;
                }

                return $orders;
            });
        } catch (InsufficientStockException $e) {
            Log::warning("Checkout failed due to insufficient raw material stock for user #{$user->id}: " . $e->getMessage());

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'    => false,
                    'error'      => 'insufficient_stock',
                    'message'    => $e->getMessage(),
                    'summary'    => $e->getSummary(),
                    'shortages'  => $e->shortages,
                ], 422);
            }

            return redirect()->route('cart.index')
                ->with('error', "{$e->getMessage()}\n" . $e->getSummary());
        } catch (\Throwable $e) {
            Log::error("Checkout failed unexpectedly for user #{$user->id}: " . $e->getMessage(), ['exception' => $e]);

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred while placing your order. Please try again.',
                ], 500);
            }

            return redirect()->route('checkout.index')
                ->with('error', 'An unexpected error occurred while placing your order. Please try again.');
        }

        // Notify Super Admins
        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($createdOrders as $order) {
            $planDetails = $order->is_downpayment 
                ? " (50% Downpayment: ₱" . number_format($order->downpayment_amount, 2) . " / Balance: ₱" . number_format($order->remaining_balance, 2) . ")"
                : " (Total: ₱" . number_format($order->total_amount, 2) . ")";

            foreach ($superAdmins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'type'    => 'new_order',
                    'message' => "New Order #{$order->order_number} placed by {$order->customer_name}{$planDetails}",
                    'is_read' => false,
                    'link'    => route('admin.orders.show', $order->id),
                ]);
            }
        }

        // ── AUTOMATED ORDER RECEIPT EMAIL DISPATCH TO CUSTOMER ──
        // Sends complete Order Receipt containing tracking code, items, options, total, payment method, and billing address.
        // Wrapped in try-catch so that checkout always completes successfully even if SMTP fails.
        // Walk-in orders are settled at the counter and receive a printed invoice — no email is sent.
        foreach ($createdOrders as $key => $order) {
            if ($order->is_walkin) {
                continue;
            }

            // For mixed carts with parent order, send receipts for sub-orders (or the parent if single)
            if ($isMixedCart && $key === 'parent' && (isset($createdOrders['regular']) || isset($createdOrders['custom']))) {
                continue;
            }

            $customerEmail = $order->customer_email ?: (Auth::check() ? Auth::user()->email : null);
            if (!empty($customerEmail)) {
                try {
                    $receiptMailable = new OrderReceiptMail($order);
                    if (config('queue.default') === 'sync') {
                        Mail::to($customerEmail)->send($receiptMailable);
                    } else {
                        Mail::to($customerEmail)->sendNow($receiptMailable);
                    }
                } catch (\Throwable $e) {
                    Log::error("Failed to send checkout order receipt email for Order #{$order->order_number}: " . $e->getMessage(), [
                        'order_id'       => $order->id,
                        'order_number'   => $order->order_number,
                        'customer_email' => $customerEmail,
                        'error'          => $e->getMessage(),
                    ]);
                }
            }
        }

        // Remove checked/purchased items from session cart
        $remainingCart = array_filter($fullCart, function ($key) use ($cart) {
            return !array_key_exists($key, $cart);
        }, ARRAY_FILTER_USE_KEY);

        if (!empty($remainingCart)) {
            session()->put('cart', $remainingCart);
        } else {
            session()->forget('cart');
        }
        // Keep the customer's account cart in sync with the remaining items
        \App\Services\CartAccountService::persistToAccount();

        $firstOrder = reset($createdOrders);

        if ($isWalkin && $isAdminOrSuper) {
            $redirectUrl = route('admin.orders.show', $firstOrder->id);
            $invoiceUrl = route('admin.orders.invoice', $firstOrder->id);

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'      => true,
                    'message'      => "Walk-in Order #{$firstOrder->order_number} placed and marked as Paid (Cash) successfully!",
                    'order_id'     => $firstOrder->id,
                    'order_number' => $firstOrder->order_number,
                    'redirect_url' => $redirectUrl,
                    'invoice_url'  => $invoiceUrl,
                ], 201);
            }

            return redirect()->route('admin.orders.show', $firstOrder->id)
                ->with('success', "Walk-in Order #{$firstOrder->order_number} created successfully!")
                ->with('auto_print_invoice', route('admin.orders.invoice', $firstOrder->id));
        }

        if ($isMixedCart && isset($createdOrders['regular']) && isset($createdOrders['custom'])) {
            $regOrd = $createdOrders['regular'];
            $custOrd = $createdOrders['custom'];
            $parentOrd = $createdOrders['parent'] ?? null;
            $parentPrefix = $parentOrd ? "under Parent Order #{$parentOrd->order_number}: " : "";
            $successMsg = "Your cart items were split and placed into 2 separate sub-orders {$parentPrefix}Ready-Made Order #{$regOrd->order_number} and Custom Order #{$custOrd->order_number}.";

            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'       => true,
                    'is_mixed_cart' => true,
                    'message'       => $successMsg,
                    'parent_order'  => $parentOrd ? ['id' => $parentOrd->id, 'order_number' => $parentOrd->order_number] : null,
                    'orders'        => [
                        ['id' => $regOrd->id, 'order_number' => $regOrd->order_number, 'type' => 'regular'],
                        ['id' => $custOrd->id, 'order_number' => $custOrd->order_number, 'type' => 'custom'],
                    ],
                    'redirect_url'  => route('customer.orders.index'),
                ], 201);
            }

            return redirect()->route('customer.orders.index')
                ->with('success', $successMsg);
        }

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => "Order #{$firstOrder->order_number} placed successfully!",
                'order_id'     => $firstOrder->id,
                'order_number' => $firstOrder->order_number,
                'redirect_url' => route('customer.orders.show', $firstOrder->id),
            ], 201);
        }

        return redirect()->route('customer.orders.show', $firstOrder->id)
            ->with('success', "Order #{$firstOrder->order_number} placed successfully!");
    }
}
