<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * Display a listing of materials.
     */
    public function index()
    {
        $materials = Material::latest()->get();
        return redirect()->route('admin.dashboard');
    }

    /**
     * Store a newly created material in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:Paper,Ink,Vinyl,Sticker,Other'],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);

        if (!isset($validated['reorder_level']) || is_null($validated['reorder_level'])) {
            $validated['reorder_level'] = 10;
        }

        $material = Material::create($validated);

        return redirect()->to(route('admin.dashboard') . '#inventory-section')
            ->with('success', "Material '{$material->name}' added to inventory successfully!");
    }

    /**
     * Update the specified material in storage.
     */
    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:Paper,Ink,Vinyl,Sticker,Other'],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
        ]);

        $material->update($validated);

        return redirect()->to(route('admin.dashboard') . '#inventory-section')
            ->with('success', "Material '{$material->name}' updated successfully!");
    }

    /**
     * Remove the specified material from storage.
     */
    public function destroy(Material $material)
    {
        $name = $material->name;
        $material->delete();

        return redirect()->to(route('admin.dashboard') . '#inventory-section')
            ->with('success', "Material '{$name}' removed from inventory.");
    }
}
