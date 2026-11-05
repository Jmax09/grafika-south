<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\HeroBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuperAdminHeroController extends Controller
{
    /**
     * Display the Hero Banners list and editor for Super Admin.
     */
    public function edit()
    {
        $banners = HeroBanner::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        $nextOrder = (HeroBanner::max('sort_order') ?? 0) + 1;
        return view('super_admin.hero.edit', compact('banners', 'nextOrder'));
    }

    /**
     * Store a new Hero Banner (Super Admin ONLY).
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
            'title' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $imagePath = $request->file('image')->store('banners', 'public');
        $sortOrder = $request->filled('sort_order') 
            ? (int) $request->sort_order 
            : ((HeroBanner::max('sort_order') ?? 0) + 1);

        HeroBanner::create([
            'image_path' => $imagePath,
            'title' => $request->title,
            'link_url' => $request->link_url,
            'sort_order' => $sortOrder,
            'is_active' => $request->is_active,
        ]);

        return back()->with('success', 'Hero banner added successfully!');
    }

    /**
     * Update Hero Banner settings (Super Admin ONLY).
     */
    public function update(Request $request, $id)
    {
        $banner = HeroBanner::findOrFail($id);

        $request->validate([
            'image' => ['nullable', 'image', 'max:5120'],
            'title' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $updateData = [
            'title' => $request->title,
            'link_url' => $request->link_url,
            'is_active' => $request->is_active,
        ];

        if ($request->filled('sort_order')) {
            $updateData['sort_order'] = (int) $request->sort_order;
        }

        if ($request->hasFile('image')) {
            if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $updateData['image_path'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($updateData);

        return back()->with('success', 'Hero banner updated successfully!');
    }

    /**
     * Toggle active status of a Hero Banner.
     */
    public function toggle($id)
    {
        $banner = HeroBanner::findOrFail($id);
        $banner->is_active = !$banner->is_active;
        $banner->save();

        $status = $banner->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Hero banner {$status} successfully!");
    }

    /**
     * Reorder all banners via sort order array or list of IDs.
     */
    public function reorder(Request $request)
    {
        if ($request->has('ids') && is_array($request->ids)) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                foreach ($request->ids as $index => $id) {
                    HeroBanner::where('id', $id)->update(['sort_order' => $index + 1]);
                }
            });
        } elseif ($request->has('orders') && is_array($request->orders)) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                foreach ($request->orders as $bannerId => $order) {
                    HeroBanner::where('id', $bannerId)->update(['sort_order' => (int) $order]);
                }
            });
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Banner display order saved successfully!',
            ]);
        }

        return back()->with('success', 'Banner display order saved successfully!');
    }

    /**
     * Delete an existing Hero Banner (Super Admin ONLY).
     */
    public function destroy($id)
    {
        $banner = HeroBanner::findOrFail($id);

        if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
            Storage::disk('public')->delete($banner->image_path);
        }

        $banner->delete();

        return back()->with('success', 'Hero banner deleted successfully!');
    }
}
