<?php

namespace App\Models;
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display the products catalog with dynamic filtering.
     */
    public function index(Request $request)
    {
        $query = Product::where('is_active', true)->with(['options', 'materials'])->withCount('reviews')->withAvg('reviews', 'rating');

        // Filter by Category
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Search by query keyword
        $searchTerm = trim($request->input('q', $request->input('search', '')));
        if (!empty($searchTerm)) {
            // Map common search phrases to categories or synonyms
            $categorySynonyms = [
                't-shirt' => ['Apparel', 'T-Shirt', 'Tshirt', 'Shirt', 'Clothing'],
                'tshirt' => ['Apparel', 'T-Shirt', 'Tshirt', 'Shirt', 'Clothing'],
                'pvc id' => ['Identification', 'ID', 'PVC', 'PVC ID', 'Badge', 'Cards'],
                'id card' => ['Identification', 'ID', 'PVC', 'PVC ID', 'Badge'],
                'business cards' => ['Marketing', 'Business Card', 'Calling Card', 'Cards', 'Business'],
                'lamination' => ['Services', 'Lamination', 'Laminating', 'Document'],
            ];

            $lowerSearch = strtolower($searchTerm);
            $synonyms = $categorySynonyms[$lowerSearch] ?? [];
            $terms = array_filter(explode(' ', $searchTerm));

            $query->where(function ($q) use ($searchTerm, $terms, $synonyms) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%')
                  ->orWhere('category', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('options', function ($oq) use ($searchTerm) {
                      $oq->where('option_value', 'like', '%' . $searchTerm . '%')
                         ->orWhere('option_name', 'like', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('materials', function ($mq) use ($searchTerm) {
                      $mq->where('name', 'like', '%' . $searchTerm . '%');
                  });

                // Match against synonyms/related categories
                foreach ($synonyms as $syn) {
                    $q->orWhere('category', 'like', '%' . $syn . '%')
                      ->orWhere('name', 'like', '%' . $syn . '%')
                      ->orWhere('description', 'like', '%' . $syn . '%');
                }

                // Match individual terms for multi-word queries
                if (count($terms) > 1) {
                    foreach ($terms as $t) {
                        $q->orWhere('name', 'like', '%' . $t . '%')
                          ->orWhere('category', 'like', '%' . $t . '%');
                    }
                }
            });
        }

        // Sort products
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price-low':
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price-high':
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'name-asc':
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name-desc':
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $products = $query->get();

        // Distinct categories for dropdown selector
        $categories = Product::where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category', 'asc')
            ->pluck('category');

        $categoryCounts = Product::where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        $totalProductsCount = Product::where('is_active', true)->count();

        return view('products.index', compact('products', 'categories', 'categoryCounts', 'totalProductsCount'));
    }

    /**
     * Display single product detail with dynamic options selector and live price estimation.
     */
    public function show($idOrSlug)
    {
        $product = Product::where('is_active', true)
            ->where(function ($q) use ($idOrSlug) {
                $q->where('id', $idOrSlug)->orWhere('slug', $idOrSlug);
            })
            ->with(['options.materials', 'materials', 'images', 'videos', 'reviews.user'])
            ->firstOrFail();

        // Group options by option_name (e.g. Size, Color, Finish)
        $groupedOptions = $product->options->groupBy('option_name');

        return view('products.show', compact('product', 'groupedOptions'));
    }
}
