<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a product review submitted from a completed order's "Rate & Review" modal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_item_id' => ['required', 'exists:order_items,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ], [
            'rating.required' => 'Please select a star rating before submitting your review.',
            'photo.image' => 'The review photo must be an image file.',
            'photo.max' => 'The review photo must not exceed 2 MB.',
        ]);

        $orderItem = OrderItem::with('order', 'product')->findOrFail($validated['order_item_id']);

        // Security: the order item must belong to the signed-in customer
        if ($orderItem->order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Reviews are only allowed once the order is completed
        if ($orderItem->order->status !== 'completed') {
            return back()->with('review_error', 'You can only review products from completed orders.');
        }

        if (!$orderItem->product_id) {
            return back()->with('review_error', 'This item is not linked to a catalog product and cannot be reviewed.');
        }

        // One review per product per order per customer
        $alreadyReviewed = Review::where('user_id', Auth::id())
            ->where('order_id', $orderItem->order_id)
            ->where('product_id', $orderItem->product_id)
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('review_error', 'You have already reviewed this product for this order.');
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('review_photos', 'public');
        }

        Review::create([
            'order_id' => $orderItem->order_id,
            'product_id' => $orderItem->product_id,
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'photo_path' => $photoPath,
        ]);

        return back()->with('review_success', 'Thank you! Your review for "' . $orderItem->product->name . '" has been submitted.');
    }
}
