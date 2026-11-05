<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    /**
     * Display products listing for Admin staff with search and filtering.
     */
    public function index(Request $request)
    {
        $query = Product::with(['options', 'materials']);

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

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Store new product (Admin staff can add, but price is set to base price).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['required', 'image', 'max:5120'],
            'requires_design_file' => ['nullable', 'boolean'],
            'requires_custom_terms' => ['nullable', 'boolean'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $slug = Str::slug($request->name) . '-' . Str::random(4);

        $product = Product::create([
            'name'                  => $request->name,
            'slug'                  => $slug,
            'category'              => $request->category,
            'description'           => $request->description,
            'price'                 => $request->price,
            'base_price'            => $request->price,
            'image_path'            => $imagePath,
            'is_active'             => false, // Draft by default — must be explicitly published
            'requires_design_file'  => $request->boolean('requires_design_file', false),
            'requires_custom_terms' => $request->boolean('requires_custom_terms', false),
        ]);

        return back()->with('success', "📝 Draft created for '{$product->name}'. It is hidden from the public catalog. A Super Admin must configure its options & BOM, then set Status → Active to publish it.");
    }

    /**
     * Edit product info (Admin staff cannot change core base pricing or delete).
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'requires_design_file' => ['nullable', 'boolean'],
            'requires_custom_terms' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
        ]);

        $updateData = [
            'name' => $request->name,
            'category' => $request->category,
            'description' => $request->description,
            'is_active' => $request->is_active,
            'requires_design_file' => $request->boolean('requires_design_file', false),
            'requires_custom_terms' => $request->boolean('requires_custom_terms', false),
        ];

        if ($request->hasFile('image')) {
            $updateData['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($updateData);

        return back()->with('success', "Product '{$product->name}' updated successfully.");
    }
}
