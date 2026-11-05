<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOption;
use App\Services\CartAccountService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Store the cart in the session and mirror it to the signed-in customer's
     * account so it is restored on the next session / device.
     */
    private function persistCart(array $cart): void
    {
        session()->put('cart', $cart);
        CartAccountService::persistToAccount();
    }

    /**
     * Display the shopping cart.
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $subtotal = 0;
        $selectedCount = 0;

        foreach ($cart as $item) {
            $isSelected = !isset($item['selected']) || $item['selected'] === true;
            if ($isSelected) {
                $subtotal += $item['price'] * $item['quantity'];
                $selectedCount++;
            }
        }

        $total = $subtotal;

        return view('customer.cart', compact(
            'cart',
            'subtotal',
            'selectedCount',
            'total'
        ));
    }

    /**
     * Add a product to the cart with dynamic option selections and optional design artwork upload.
     */
    public function add(Request $request)
    {
        $product = Product::with(['options.materials', 'materials'])->findOrFail($request->product_id);

        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'options' => ['nullable', 'array'],
            // Multiple artwork/design files may be attached (array), a single one is
            // still accepted for backward compatibility with older forms.
            'design_files' => [$product->requires_design_file ? 'required_without:design_file' : 'nullable', 'array', 'max:10'],
            'design_files.*' => ['file', 'mimes:jpeg,jpg,png,pdf,ai,psd,svg,zip,eps,cdr', 'max:20480'], // 20MB max each
            'design_file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf,ai,psd,svg,zip,eps,cdr', 'max:20480'],
            'special_instructions' => ['nullable', 'string', 'max:1000'],
        ], [
            'design_files.required' => 'Please upload or attach your artwork design file before adding this item to your cart.',
            'design_files.*.max' => 'Each uploaded artwork file must not exceed 20 MB.',
            'design_files.*.mimes' => 'Each uploaded artwork file must be a valid format: PNG, JPG, PDF, AI, PSD, SVG, or ZIP.',
            'design_file.max' => 'The uploaded artwork file must not exceed 20 MB.',
            'design_file.mimes' => 'The uploaded artwork file must be a valid format: PNG, JPG, PDF, AI, PSD, SVG, or ZIP.',
        ]);

        $basePrice = (float) ($product->price ?: $product->base_price);
        $totalPriceModifier = 0;
        $selectedOptionsFormatted = [];

        if ($request->filled('options')) {
            foreach ($request->options as $optionName => $optionValue) {
                // Find matching option model for price modifier
                $option = ProductOption::where('product_id', $product->id)
                    ->where('option_name', $optionName)
                    ->where('option_value', $optionValue)
                    ->first();

                $modifier = $option ? (float) $option->price_modifier : 0;
                $totalPriceModifier += $modifier;

                $modifierStr = $modifier > 0 ? " (+₱" . number_format($modifier, 2) . ")" : "";
                $selectedOptionsFormatted[$optionName] = $optionValue . $modifierStr;
            }
        }

        $effectiveUnitPrice = $basePrice + $totalPriceModifier;

        // Unique cart item key based on product ID + options hash
        $optionsHash = md5(json_encode($selectedOptionsFormatted));
        $cartKey = 'prod_' . $product->id . '_' . $optionsHash;
        $cart = session()->get('cart', []);

        $requestedQty = (int) $request->quantity;
        $currentCartQty = isset($cart[$cartKey]) ? (int) $cart[$cartKey]['quantity'] : 0;
        $targetTotalQty = $currentCartQty + $requestedQty;

        // Validate BOM Raw Material Stock
        if (!$product->checkBOMStock($targetTotalQty, $request->options)) {
            $shortages = $product->getBOMShortages($targetTotalQty, $request->options);
            $shortageLines = [];
            foreach ($shortages as $s) {
                $shortageLines[] = "• {$s['material']}: requires {$s['required']} {$s['unit']}, only {$s['available']} {$s['unit']} available";
            }
            $shortageMsg = !empty($shortageLines)
                ? "\n" . implode("\n", $shortageLines)
                : "";

            return back()->with('error', "Unable to add {$product->name} to cart. Insufficient raw material inventory for {$targetTotalQty} units.{$shortageMsg}");
        }

        // Handle file upload(s) if provided directly when adding to cart
        // (multiple artwork/design files are supported)
        $uploadedFilePaths = [];
        $designFiles = $request->file('design_files');
        if (is_array($designFiles)) {
            foreach ($designFiles as $designFile) {
                if ($designFile && $designFile->isValid()) {
                    $uploadedFilePaths[] = $designFile->store('order_files_temp', 'public');
                }
            }
        }
        if (empty($uploadedFilePaths) && $request->hasFile('design_file')) {
            $file = $request->file('design_file');
            if ($file && $file->isValid()) {
                $uploadedFilePaths[] = $file->store('order_files_temp', 'public');
            }
        }
        // Keep the first path under the legacy key for backward compatibility
        $uploadedFilePath = $uploadedFilePaths[0] ?? null;

        $isCustomItem = (bool) $product->requires_design_file || (bool) $product->requires_custom_terms || !empty($uploadedFilePath)
            || ($product->productionTimeEnabled() && (int) ($product->custom_production_time_minutes ?? 0) > 0);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $requestedQty;
            $cart[$cartKey]['selected'] = true; // Selected by default
            if ($uploadedFilePath) {
                $cart[$cartKey]['design_file_path'] = $uploadedFilePath;
            }
            if (!empty($uploadedFilePaths)) {
                $existingPaths = $cart[$cartKey]['design_file_paths'] ?? [];
                $cart[$cartKey]['design_file_paths'] = array_values(array_unique(array_merge($existingPaths, $uploadedFilePaths)));
                $cart[$cartKey]['design_file_path'] = $cart[$cartKey]['design_file_paths'][0] ?? $uploadedFilePath;
            }
            $totalItemQty = (int) $cart[$cartKey]['quantity'];
            $prodTimeMinutes = $isCustomItem ? $product->getEstimatedProductionTime($totalItemQty, $selectedOptionsFormatted) : null;
            $cart[$cartKey]['production_time_minutes']   = $prodTimeMinutes;
            $cart[$cartKey]['formatted_production_time'] = $prodTimeMinutes > 0 ? \App\Models\Product::formatMinutes($prodTimeMinutes) : null;
            $cart[$cartKey]['is_custom']                 = $isCustomItem;
        } else {
            $prodTimeMinutes = $isCustomItem ? $product->getEstimatedProductionTime($requestedQty, $selectedOptionsFormatted) : null;
            $cart[$cartKey] = [
                'cart_key'                    => $cartKey,
                'product_id'                  => $product->id,
                'name'                        => $product->name,
                'price'                       => $effectiveUnitPrice,
                'base_price'                  => $basePrice,
                'quantity'                    => $requestedQty,
                'image_path'                  => $product->image_path,
                'category'                    => $product->category,
                'options'                     => $selectedOptionsFormatted,
                'design_file_path'            => $uploadedFilePath,
                'design_file_paths'           => $uploadedFilePaths,
                'requires_design_file'        => (bool) $product->requires_design_file,
                'requires_custom_terms'       => (bool) $product->requires_custom_terms,
                'production_time_minutes'     => $prodTimeMinutes,
                'formatted_production_time'   => $prodTimeMinutes > 0 ? \App\Models\Product::formatMinutes($prodTimeMinutes) : null,
                'special_instructions'        => $request->input('special_instructions', ''),
                'selected'                    => true,
                'is_custom'                   => $isCustomItem,
            ];
        }


        $this->persistCart($cart);

        return redirect()->route('cart.index')
            ->with('success', "{$product->name} added to your cart!");
    }

    /**
     * Update item quantity in cart.
     */
    public function update(Request $request)
    {
        $request->validate([
            'key' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->key])) {
            $newQuantity = (int) $request->quantity;
            $productId = $cart[$request->key]['product_id'];
            $product = Product::with(['materials', 'options.materials'])->find($productId);

            if ($product && !$product->checkBOMStock($newQuantity, $cart[$request->key]['options'] ?? null)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot update quantity: Insufficient raw material stock for {$newQuantity} units of {$product->name}."
                    ], 422);
                }
                return redirect()->route('cart.index')
                    ->with('error', "Cannot update quantity: Insufficient raw material stock for {$newQuantity} units of {$product->name}.");
            }

            $cart[$request->key]['quantity'] = $newQuantity;
            if ($product) {
                $isCustom = !empty($cart[$request->key]['requires_design_file']) || !empty($cart[$request->key]['requires_custom_terms']) || !empty($cart[$request->key]['design_file_path']) || $product->is_customizable;
                if ($isCustom) {
                    $prodTimeMinutes = $product->getEstimatedProductionTime($newQuantity, $cart[$request->key]['options'] ?? null);
                    $cart[$request->key]['production_time_minutes'] = $prodTimeMinutes;
                    $cart[$request->key]['formatted_production_time'] = $prodTimeMinutes > 0 ? \App\Models\Product::formatMinutes($prodTimeMinutes) : null;
                }
            }
            $this->persistCart($cart);

            $subtotal = 0;
            $selectedCount = 0;
            foreach ($cart as $item) {
                if (!isset($item['selected']) || $item['selected'] === true) {
                    $subtotal += $item['price'] * $item['quantity'];
                    $selectedCount++;
                }
            }
            $itemTotal = $cart[$request->key]['price'] * $newQuantity;

            // Voucher module retired: no discount is applied at cart level.
            $discount = 0;
            $total = $subtotal;

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cart updated successfully.',
                    'cart_count' => count($cart),
                    'selected_count' => $selectedCount,
                    'subtotal' => $subtotal,
                    'formatted_subtotal' => '₱' . number_format($subtotal, 2),
                    'discount' => $discount,
                    'formatted_discount' => '-₱' . number_format($discount, 2),
                    'total' => $total,
                    'formatted_total' => '₱' . number_format($total, 2),
                    'voucher_error' => null,
                    'item_total' => $itemTotal,
                    'formatted_item_total' => '₱' . number_format($itemTotal, 2),
                    'production_time_minutes' => $cart[$request->key]['production_time_minutes'] ?? null,
                    'formatted_production_time' => $cart[$request->key]['formatted_production_time'] ?? null,
                ]);
            }

            return redirect()->route('cart.index')->with('success', 'Cart updated successfully.');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Item not found in cart.'], 404);
        }

        return redirect()->route('cart.index')->with('error', 'Item not found in cart.');
    }

    /**
     * Toggle or set an individual cart item's selection status.
     */
    public function toggleSelect(Request $request)
    {
        $request->validate([
            'key' => ['required', 'string'],
            'selected' => ['nullable', 'boolean'],
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->key])) {
            if ($request->has('selected')) {
                $cart[$request->key]['selected'] = (bool) $request->boolean('selected');
            } else {
                $current = !isset($cart[$request->key]['selected']) || $cart[$request->key]['selected'] === true;
                $cart[$request->key]['selected'] = !$current;
            }

            $this->persistCart($cart);

            $subtotal = 0;
            $selectedCount = 0;
            foreach ($cart as $item) {
                if (!isset($item['selected']) || $item['selected'] === true) {
                    $subtotal += $item['price'] * $item['quantity'];
                    $selectedCount++;
                }
            }

            // Voucher module retired: no discount is applied at cart level.
            $discount = 0;
            $total = $subtotal;

            return response()->json([
                'success' => true,
                'key' => $request->key,
                'selected' => $cart[$request->key]['selected'],
                'selected_count' => $selectedCount,
                'cart_count' => count($cart),
                'subtotal' => $subtotal,
                'formatted_subtotal' => '₱' . number_format($subtotal, 2),
                'discount' => $discount,
                'formatted_discount' => '-₱' . number_format($discount, 2),
                'total' => $total,
                'formatted_total' => '₱' . number_format($total, 2),
                'voucher_error' => null,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Item not found in cart.'], 404);
    }

    /**
     * Select or deselect all items in the cart.
     */
    public function selectAll(Request $request)
    {
        $request->validate([
            'selected' => ['required', 'boolean'],
        ]);

        $selected = (bool) $request->boolean('selected');
        $cart = session()->get('cart', []);

        foreach ($cart as $key => $item) {
            $cart[$key]['selected'] = $selected;
        }

        $this->persistCart($cart);

        $subtotal = 0;
        $selectedCount = 0;
        if ($selected) {
            foreach ($cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
                $selectedCount++;
            }
        }

        // Voucher module retired: no discount is applied at cart level.
        $discount = 0;
        $total = $subtotal;

        return response()->json([
            'success' => true,
            'selected' => $selected,
            'selected_count' => $selectedCount,
            'cart_count' => count($cart),
            'subtotal' => $subtotal,
            'formatted_subtotal' => '₱' . number_format($subtotal, 2),
            'discount' => $discount,
            'formatted_discount' => '-₱' . number_format($discount, 2),
            'total' => $total,
            'formatted_total' => '₱' . number_format($total, 2),
            'voucher_error' => null,
        ]);
    }

    /**
     * Remove an item from cart.
     */
    public function remove(Request $request, $key)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$key])) {
            unset($cart[$key]);
            $this->persistCart($cart);

            $subtotal = 0;
            $selectedCount = 0;
            foreach ($cart as $item) {
                if (!isset($item['selected']) || $item['selected'] === true) {
                    $subtotal += $item['price'] * $item['quantity'];
                    $selectedCount++;
                }
            }

            // Voucher module retired: no discount is applied at cart level.
            $discount = 0;
            $total = $subtotal;

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item removed from cart.',
                    'cart_count' => count($cart),
                    'selected_count' => $selectedCount,
                    'subtotal' => $subtotal,
                    'formatted_subtotal' => '₱' . number_format($subtotal, 2),
                    'discount' => $discount,
                    'formatted_discount' => '-₱' . number_format($discount, 2),
                    'total' => $total,
                    'formatted_total' => '₱' . number_format($total, 2),
                    'voucher_error' => null,
                    'empty' => count($cart) === 0
                ]);
            }

            return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Item not found in cart.'], 404);
        }

        return redirect()->route('cart.index')->with('error', 'Item not found in cart.');
    }

    /**
     * Clear all items from cart.
     */
    public function clear(Request $request)
    {
        session()->forget('cart');
        session()->forget('applied_voucher');
        CartAccountService::forgetAccountCart();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Shopping cart cleared.',
                'cart_count' => 0,
                'selected_count' => 0,
                'subtotal' => 0,
                'formatted_subtotal' => '₱0.00',
                'discount' => 0,
                'total' => 0,
                'formatted_total' => '₱0.00',
                'empty' => true
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Shopping cart cleared.');
    }

    /**
     * Remove only the selected cart items (bulk remove by keys).
     */
    public function removeSelected(Request $request)
    {
        $request->validate([
            'keys' => ['required', 'array'],
            'keys.*' => ['string'],
        ]);

        $cart = session()->get('cart', []);

        foreach ($request->keys as $key) {
            unset($cart[$key]);
        }
        $this->persistCart($cart);

        $subtotal = 0;
        $selectedCount = 0;
        foreach ($cart as $item) {
            if (!isset($item['selected']) || $item['selected'] === true) {
                $subtotal += $item['price'] * $item['quantity'];
                $selectedCount++;
            }
        }

        // Voucher module retired: no discount is applied at cart level.
        $discount = 0;
        $total = $subtotal;

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Selected items removed from cart.',
                'cart_count' => count($cart),
                'selected_count' => $selectedCount,
                'subtotal' => $subtotal,
                'formatted_subtotal' => '₱' . number_format($subtotal, 2),
                'discount' => $discount,
                'formatted_discount' => '-₱' . number_format($discount, 2),
                'total' => $total,
                'formatted_total' => '₱' . number_format($total, 2),
                'voucher_error' => null,
                'empty' => count($cart) === 0
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Selected items removed from cart.');
    }
}
