<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductMaterial;
use App\Models\ProductOption;
use App\Models\ProductOptionMaterial;
use App\Models\ProductVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SuperAdminProductController extends Controller
{
    /**
     * Display full products list for Super Admin with search and filtering.
     */
    public function index(Request $request)
    {
        $query = Product::with(['options', 'materials', 'productMaterials.material']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $products = $query->latest()->get();
        $categories = Product::select('category')->distinct()->pluck('category')->filter();
        $materials = Material::all();

        return view('super_admin.products.index', compact('products', 'categories', 'materials'));
    }

    /**
     * Display Product Detail and BOM Editor view.
     */
    public function edit($id)
    {
        $product = Product::with(['options.materials', 'productMaterials.material', 'images', 'videos'])->findOrFail($id);
        $allMaterials = Material::all();
        $categories = Product::select('category')->distinct()->pluck('category')->filter()->sort()->values();

        // ── Auto-migrate legacy image_path → product_images gallery ──
        // If the product has an image_path but no product_images rows yet,
        // seed the first gallery record from it so the thumbnail grid renders.
        if ($product->image_path && $product->images->isEmpty()) {
            $img = ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $product->image_path,
                'sort_order' => 1,
                'is_primary' => true,
            ]);
            // Reload the images relationship so the view gets fresh data
            $product->load('images');
        }

        // Collect distinct option names used across all products for convenient choices
        $existingOptionNames = ProductOption::select('option_name')
            ->distinct()
            ->pluck('option_name')
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        if (empty($existingOptionNames)) {
            $existingOptionNames = ['Size', 'Color', 'Material', 'Finish', 'Type'];
        }

        return view('super_admin.products.edit', compact('product', 'allMaterials', 'existingOptionNames', 'categories'));
    }

    /**
     * Show the full-page form for creating a new product.
     */
    public function create()
    {
        $categories = Product::select('category')->distinct()->pluck('category')->filter();
        return view('super_admin.products.create', compact('categories'));
    }

    /**
     * Instantly create a blank draft product and redirect to its edit/BOM page.
     */
    public function quickCreate()
    {
        $product = Product::create([
            'name'                           => 'New Product',
            'slug'                           => 'new-product-' . Str::random(6),
            'category'                       => 'Uncategorized',
            'description'                    => '',
            'price'                          => 0,
            'base_price'                     => 0,
            'image_path'                     => null,
            'is_active'                      => false,
            'is_featured'                    => false,
            'requires_design_file'           => false,
            'requires_custom_terms'          => false,
            'custom_production_time_minutes' => null,
            'enable_production_time'         => false,
        ]);

        return redirect()->route('super-admin.products.edit', $product->id)
            ->with('success', '🆕 New draft product created. Fill in the details and configure BOM below.');
    }

    /**
     * Store new product with pricing.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['required', 'image', 'max:5120'],
            'custom_production_time_minutes' => ['nullable', 'integer', 'min:0'],
            'enable_production_time' => ['nullable', 'boolean'],
            'requires_custom_terms' => ['nullable', 'boolean'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name'                  => $request->name,
            'slug'                  => Str::slug($request->name) . '-' . Str::random(4),
            'category'              => $request->category,
            'description'           => $request->description,
            'price'                 => $request->price,
            'base_price'            => $request->price,
            'image_path'            => $imagePath,
            'is_active'             => false,   // ← Draft by default; admin must explicitly publish
            'is_featured'           => false,
            'requires_design_file'  => $request->boolean('requires_design_file', false),
            'requires_custom_terms' => $request->boolean('requires_custom_terms', false),
            'custom_production_time_minutes' => $request->filled('custom_production_time_minutes') ? (int) $request->custom_production_time_minutes : null,
            'enable_production_time' => $request->boolean('enable_production_time', true),
        ]);

        return redirect()->route('super-admin.products.edit', $product->id)
            ->with('success', "✏️ Draft created for '{$product->name}'. Configure its options & BOM, then set Status → Active to publish it to the catalog.");
    }

    /**
     * Update product details & pricing.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'requires_design_file' => ['nullable', 'boolean'],
            'requires_custom_terms' => ['nullable', 'boolean'],
            'custom_production_time_minutes' => ['nullable', 'integer', 'min:0'],
            'enable_production_time' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $masterOn = $request->boolean('enable_production_time', $product->enable_production_time ?? true);

        $updateData = [
            'name' => $request->name,
            'category' => $request->category,
            'description' => $request->description,
            'price' => $request->price,
            'base_price' => $request->price,
            'is_active' => $request->is_active,
            'is_featured' => $request->has('is_featured') ? $request->boolean('is_featured') : $product->is_featured,
            // Master switch drives all three custom-item flags together
            'enable_production_time' => $masterOn,
            'requires_design_file'   => $masterOn,
            'requires_custom_terms'  => $masterOn,
            'custom_production_time_minutes' => $request->filled('custom_production_time_minutes') ? (int) $request->custom_production_time_minutes : null,
        ];

        if ($request->hasFile('image')) {
            $updateData['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($updateData);

        return redirect()->route('super-admin.products.edit', $product->id)->with('success', "Product details for '{$product->name}' updated successfully.");

    }

    /**
     * Delete product (Super Admin only).
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return redirect()->route('super-admin.products.index')
            ->with('success', "Product '{$product->name}' deleted.");
    }

    /**
     * Quickly flip the published/draft status of a product from the product list.
     */
    public function togglePublish($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);

        $icon    = $product->is_active ? '✅' : '⛔';
        $message = $product->is_active
            ? "{$icon} '{$product->name}' is now Active — visible in the public catalog."
            : "{$icon} '{$product->name}' set to Draft — hidden from the public catalog.";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Save/Update Product Option (e.g. Size L -> +₱20.00).
     */
    public function saveOption(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'option_name' => ['required', 'string', 'max:100'],
            'option_value' => ['required', 'string', 'max:100'],
            'price_modifier' => ['required', 'numeric'],
        ]);

        ProductOption::create([
            'product_id' => $product->id,
            'option_name' => $request->option_name,
            'option_value' => $request->option_value,
            'price_modifier' => $request->price_modifier,
        ]);

        return back()->with('success', "Added option '{$request->option_name}: {$request->option_value}' to {$product->name}.");
    }

    /**
     * Delete Product Option.
     */
    public function deleteOption(Request $request, $optionId)
    {
        $option = ProductOption::findOrFail($optionId);

        if ($option->image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($option->image_path);
        }
        $option->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Option deleted.']);
        }

        return back()->with('success', 'Product option removed.');
    }

    /**
     * Define / Update Bill of Materials (BOM) per product using simple repeatable form submission.
     * Select material -> input quantity used per unit produced.
     */
    public function updateBom(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => ['required', 'exists:materials,id'],
            'materials.*.quantity_used' => ['required', 'numeric', 'min:0.0001'],
        ]);

        // Wipe existing BOM pivot records for clean update
        ProductMaterial::where('product_id', $product->id)->delete();

        if ($request->filled('materials')) {
            foreach ($request->materials as $matData) {
                if (!empty($matData['material_id']) && (float)$matData['quantity_used'] > 0) {
                    ProductMaterial::create([
                        'product_id' => $product->id,
                        'material_id' => $matData['material_id'],
                        'quantity_used' => $matData['quantity_used'],
                    ]);
                }
            }
        }

        return back()->with('success', "Bill of Materials (BOM) updated for '{$product->name}'!");
    }

    /**
     * Define / Update Bill of Materials (BOM) per product option.
     */
    public function updateOptionBom(Request $request, $optionId)
    {
        $option = ProductOption::findOrFail($optionId);

        $request->validate([
            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => ['required', 'exists:materials,id'],
            'materials.*.quantity_used' => ['required', 'numeric', 'min:0.0001'],
        ]);

        // Wipe existing BOM pivot records for clean update
        ProductOptionMaterial::where('product_option_id', $option->id)->delete();

        if ($request->filled('materials')) {
            foreach ($request->materials as $matData) {
                if (!empty($matData['material_id']) && (float)$matData['quantity_used'] > 0) {
                    ProductOptionMaterial::create([
                        'product_option_id' => $option->id,
                        'material_id' => $matData['material_id'],
                        'quantity_used' => $matData['quantity_used'],
                    ]);
                }
            }
        }

        return back()->with('success', "BOM updated for option '{$option->option_name}: {$option->option_value}'!");
    }

    /**
     * Master Save action for Product Core details, BOM, and Options (including option BOMs).
     */
    public function masterSave(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'requires_design_file' => ['nullable', 'boolean'],
            'requires_custom_terms' => ['nullable', 'boolean'],
            'custom_production_time_minutes' => ['nullable', 'integer', 'min:0'],
            'enable_production_time' => ['nullable', 'boolean'],
            // images are handled separately via gallery AJAX upload

            // Product BOM
            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => ['required', 'exists:materials,id'],
            'materials.*.quantity_used' => ['required', 'numeric', 'min:0.0001'],

            // Product Options
            'options' => ['nullable', 'array'],
            'options.*.id' => ['nullable'],
            'options.*.option_name' => ['required', 'string', 'max:100'],
            'options.*.option_value' => ['required', 'string', 'max:100'],
            'options.*.price_modifier' => ['required', 'numeric'],
            'options.*.has_custom_bom' => ['nullable'],
            'options.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'options.*.remove_image' => ['nullable'],
            
            'options.*.materials' => ['nullable', 'array'],
            'options.*.materials.*.material_id' => ['required', 'exists:materials,id'],
            'options.*.materials.*.quantity_used' => ['required', 'numeric', 'min:0.0001'],
        ]);

        DB::transaction(function () use ($request, $product) {
            // 1. Update Core Details
            $updateData = [
                'name' => $request->name,
                'category' => $request->category,
                'description' => $request->description,
                'price' => $request->price,
                'base_price' => $request->price,
                'is_active' => $request->is_active,
                'requires_design_file' => $request->boolean('requires_design_file', false),
                'requires_custom_terms' => $request->boolean('requires_custom_terms', false),
                'custom_production_time_minutes' => $request->filled('custom_production_time_minutes') ? (int) $request->custom_production_time_minutes : null,
                'enable_production_time' => $request->boolean('enable_production_time', $product->enable_production_time ?? true),
            ];

            $product->update($updateData);


            // 2. Sync Product BOM
            ProductMaterial::where('product_id', $product->id)->delete();
            if ($request->filled('materials')) {
                foreach ($request->materials as $matData) {
                    if (!empty($matData['material_id']) && (float)$matData['quantity_used'] > 0) {
                        ProductMaterial::create([
                            'product_id' => $product->id,
                            'material_id' => $matData['material_id'],
                            'quantity_used' => $matData['quantity_used'],
                        ]);
                    }
                }
            }

            // 3. Sync Product Options and their BOMs
            $existingOptionIds = ProductOption::where('product_id', $product->id)->pluck('id')->toArray();
            $submittedOptionIds = [];

            if ($request->has('options') && is_array($request->options) && !empty($request->options)) {
                foreach ($request->options as $optIndex => $optData) {
                    $optionId = !empty($optData['id']) ? (int)$optData['id'] : null;
                    $hasCustomBom = filter_var($optData['has_custom_bom'] ?? false, FILTER_VALIDATE_BOOLEAN);

                    if ($optionId && in_array($optionId, $existingOptionIds, true)) {
                        // ── UPDATE existing option ──
                        $option = ProductOption::find($optionId);
                        if ($option) {
                            $option->update([
                                'option_name'    => $optData['option_name'],
                                'option_value'   => $optData['option_value'],
                                'price_modifier' => $optData['price_modifier'],
                            ]);
                            $submittedOptionIds[] = (int) $optionId;
                        }
                    } else {
                        // ── CREATE new option ──
                        $option   = ProductOption::create([
                            'product_id'     => $product->id,
                            'option_name'    => $optData['option_name'],
                            'option_value'   => $optData['option_value'],
                            'price_modifier' => $optData['price_modifier'],
                        ]);
                        $optionId = (int) $option->id;
                        $submittedOptionIds[] = $optionId;
                    }

                    // ── Sync optional option image ──
                    // A freshly picked file replaces (and deletes) the stored one,
                    // remove_image clears it, and nothing submitted keeps it as-is.
                    $imageFile = $request->file('options.' . $optIndex . '.image');
                    if ($imageFile) {
                        $previousPath = $option->image_path;
                        $option->image_path = $imageFile->store('products/options', 'public');
                        $option->save();

                        if ($previousPath && $previousPath !== $option->image_path) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($previousPath);
                        }
                    } elseif (filter_var($optData['remove_image'] ?? false, FILTER_VALIDATE_BOOLEAN) && $option->image_path) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($option->image_path);
                        $option->update(['image_path' => null]);
                    }

                    // ── Sync option-level BOM ──
                    // If custom BOM override is specified, persist custom materials;
                    // otherwise remove option-level BOM rows so it cleanly inherits main BOM.
                    ProductOptionMaterial::where('product_option_id', $optionId)->delete();

                    if ($hasCustomBom && !empty($optData['materials']) && is_array($optData['materials'])) {
                        foreach ($optData['materials'] as $optMatData) {
                            if (!empty($optMatData['material_id']) && (float)$optMatData['quantity_used'] > 0) {
                                ProductOptionMaterial::create([
                                    'product_option_id' => $optionId,
                                    'material_id'       => $optMatData['material_id'],
                                    'quantity_used'     => $optMatData['quantity_used'],
                                ]);
                            }
                        }
                    }
                }

                // Delete options that were deleted in the UI
                $optionsToDelete = array_diff($existingOptionIds, $submittedOptionIds);
                if (!empty($optionsToDelete)) {
                    foreach (ProductOption::whereIn('id', $optionsToDelete)->whereNotNull('image_path')->get() as $orphan) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($orphan->image_path);
                    }
                    ProductOption::whereIn('id', $optionsToDelete)->delete();
                }
            }
            // NOTE: We intentionally do NOT delete existing options when `options` is absent or empty
            // from the submitted form. Options are only removed individually via the dedicated
            // DELETE AJAX endpoint (deleteOptionRow). This prevents accidental data loss if the
            // frontend fails to serialize options into FormData for any reason.
        });

        $product->refresh()->load(['options.materials', 'productMaterials.material']);

        $mainBomMaterials = $product->productMaterials->map(fn($pm) => [
            'material_id'   => $pm->material_id,
            'material_name' => $pm->material->name ?? '',
            'unit'          => $pm->material->unit ?? '',
            'quantity_used' => (float) $pm->quantity_used,
        ])->values()->toArray();

        $optionsData = $product->options->map(function ($o) {
            $hasCustomBom = $o->materials->isNotEmpty();
            return [
                'id'             => $o->id,
                'option_name'    => $o->option_name,
                'option_value'   => $o->option_value,
                'price_modifier' => (float) $o->price_modifier,
                'has_custom_bom' => $hasCustomBom,
                'image_url'      => $o->image_url,
                'materials'      => $hasCustomBom ? $o->materials->map(fn($m) => [
                    'material_id'   => $m->id,
                    'material_name' => $m->name,
                    'unit'          => $m->unit,
                    'quantity_used' => (float) $m->pivot->quantity_used,
                ])->values()->toArray() : [],
            ];
        })->values()->toArray();

        return response()->json([
            'success'   => true,
            'message'   => 'Product updated successfully.',
            'image_url' => $product->image_path ? asset('storage/' . $product->image_path) : null,
            'options'   => $optionsData,
        ]);
    }

    /**
     * Upload additional gallery images for a product (AJAX).
     */
    public function uploadGalleryImages(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'gallery_images'   => ['required', 'array', 'max:10'],
            'gallery_images.*' => ['required', 'image', 'max:5120'],
        ]);

        $sortStart = ($product->images()->max('sort_order') ?? 0) + 1;
        $posStart  = max((int) $product->images()->max('position'), (int) $product->videos()->max('position')) + 1;
        $uploaded  = [];

        foreach ($request->file('gallery_images') as $idx => $file) {
            $path      = $file->store('products/gallery', 'public');
            $isPrimary = $product->images()->count() === 0 && $idx === 0;

            $img = ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
                'sort_order' => $sortStart + $idx,
                'position'   => $posStart + $idx,
                'is_primary' => $isPrimary,
            ]);

            if ($isPrimary) {
                $product->update(['image_path' => $path]);
            }

            $uploaded[] = ['id' => $img->id, 'url' => $img->url, 'is_primary' => $img->is_primary];
        }

        return response()->json(['success' => true, 'images' => $uploaded]);
    }

    /**
     * Delete a single gallery image (AJAX).
     */
    public function deleteGalleryImage($imageId)
    {
        $img      = ProductImage::findOrFail($imageId);
        $product  = $img->product;
        $wasPrimary = $img->is_primary;

        \Illuminate\Support\Facades\Storage::disk('public')->delete($img->image_path);
        $img->delete();

        if ($wasPrimary) {
            $next = $product->images()->orderBy('sort_order')->first();
            if ($next) {
                $next->update(['is_primary' => true]);
                $product->update(['image_path' => $next->image_path]);
            } else {
                $product->update(['image_path' => null]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Set a gallery image as the primary product image (AJAX).
     */
    public function setPrimaryImage($imageId)
    {
        $img     = ProductImage::findOrFail($imageId);
        $product = $img->product;

        ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
        $img->update(['is_primary' => true]);
        $product->update(['image_path' => $img->image_path]);

        return response()->json(['success' => true]);
    }

    /**
     * Upload showcase videos for a product (AJAX).
     */
    public function uploadProductVideos(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'product_videos'   => ['required', 'array', 'max:5'],
            'product_videos.*' => ['required', 'file', 'mimes:mp4,webm,mov,avi,m4v', 'max:51200'],
        ]);

        $sortStart = ($product->videos()->max('sort_order') ?? 0) + 1;
        $posStart  = max((int) $product->images()->max('position'), (int) $product->videos()->max('position')) + 1;
        $uploaded  = [];

        foreach ($request->file('product_videos') as $idx => $file) {
            $path = $file->store('products/videos', 'public');

            $video = ProductVideo::create([
                'product_id' => $product->id,
                'video_path' => $path,
                'title'      => Str::limit(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 80, ''),
                'sort_order' => $sortStart + $idx,
                'position'   => $posStart + $idx,
            ]);

            $uploaded[] = [
                'id'         => $video->id,
                'url'        => $video->url,
                'ext'        => $video->extension,
                'title'      => $video->title,
                'is_primary' => $video->is_primary,
            ];
        }

        return response()->json(['success' => true, 'videos' => $uploaded]);
    }

    /**
     * Delete a single showcase video (AJAX).
     */
    public function deleteProductVideo($videoId)
    {
        $video = ProductVideo::findOrFail($videoId);

        \Illuminate\Support\Facades\Storage::disk('public')->delete($video->video_path);
        $video->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Set a showcase video as the product's primary ("Main") media (AJAX).
     * Only one media item across images + videos can be Main, so the
     * product's image gallery primaries are cleared as well.
     */
    public function setPrimaryVideo($videoId)
    {
        $video   = ProductVideo::findOrFail($videoId);
        $product = $video->product;

        ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
        ProductVideo::where('product_id', $product->id)->update(['is_primary' => false]);
        $video->update(['is_primary' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Persist a drag-to-reorder of the combined media (images + videos).
     * The FIRST item in the received order automatically becomes the
     * product's primary ("Main") media.
     */
    public function reorderProductMedia(Request $request, $id)
    {
        $product = Product::with(['images', 'videos'])->findOrFail($id);

        $request->validate([
            'order'         => ['required', 'array'],
            'order.*.type'  => ['required', 'in:image,video'],
            'order.*.id'    => ['required', 'integer'],
        ]);

        $items      = collect($request->input('order'));
        $imgIds     = $product->images->pluck('id')->all();
        $vidIds     = $product->videos->pluck('id')->all();
        $imgPos     = 0;
        $vidPos     = 0;
        $firstType  = null;
        $firstId    = null;

        foreach ($items as $i => $it) {
            if ($i === 0) {
                $firstType = $it['type'];
                $firstId   = (int) $it['id'];
            }
            $globalPos = $i + 1; // unified position across images + videos
            if ($it['type'] === 'image' && in_array((int) $it['id'], $imgIds)) {
                ProductImage::where('id', $it['id'])->update([
                    'position'   => $globalPos,
                    'sort_order' => ++$imgPos,
                ]);
            } elseif ($it['type'] === 'video' && in_array((int) $it['id'], $vidIds)) {
                ProductVideo::where('id', $it['id'])->update([
                    'position'   => $globalPos,
                    'sort_order' => ++$vidPos,
                ]);
            }
        }

        // Clear all primaries, then set the first item as Main.
        ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
        ProductVideo::where('product_id', $product->id)->update(['is_primary' => false]);

        if ($firstType === 'video') {
            ProductVideo::where('id', $firstId)->update(['is_primary' => true]);
        } elseif ($firstType === 'image') {
            $img = ProductImage::find($firstId);
            if ($img) {
                $img->update(['is_primary' => true]);
                $product->update(['image_path' => $img->image_path]);
            }
        }

        return response()->json(['success' => true]);
    }
}
