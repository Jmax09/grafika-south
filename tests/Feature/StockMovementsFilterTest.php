<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementsFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_by_type_restock()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $material = Material::create([
            'name' => 'Sample Material',
            'category' => 'Ink',
            'unit' => 'ml',
            'current_stock' => 100,
            'low_stock_threshold' => 10,
        ]);

        MaterialLog::create([
            'material_id' => $material->id,
            'quantity_deducted' => -50,
            'stock_before' => 50,
            'stock_after' => 100,
            'type' => 'restock',
            'notes' => 'Restocked 50 ml',
        ]);

        MaterialLog::create([
            'material_id' => $material->id,
            'quantity_deducted' => 10,
            'stock_before' => 100,
            'stock_after' => 90,
            'type' => 'auto_deduction',
            'notes' => 'Auto deduction',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.materials.movements', ['log_type' => 'restock']));
        $response->assertStatus(200);
        $response->assertSee('Restocked 50 ml');
        $response->assertDontSee('Auto deduction');
    }
}
