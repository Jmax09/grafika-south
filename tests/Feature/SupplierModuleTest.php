<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialLog;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierMaterial;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierModuleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@test.com',
            'password' => 'password123',
            'role' => 'super_admin',
            'is_active' => true,
        ]);
    }

    /**
     * Ink stock measured in ml. (The sqlite test schema constrains materials.unit
     * to ml/pcs/sheets, so "liters" cannot be used here.)
     */
    private function inkMaterial(float $stock = 100, string $unit = 'ml'): Material
    {
        return Material::create([
            'name' => 'Sublimation Ink Black',
            'category' => 'Ink',
            'unit' => $unit,
            'current_stock' => $stock,
            'low_stock_threshold' => 20,
        ]);
    }

    /**
     * A catalog entry is an inventory package: 1 Box = 5 ml, priced per box.
     */
    private function supplierWithCatalog(?Material $material = null, string $package = 'Box', float $conversion = 5, float $unitCost = 250.00): array
    {
        $material ??= $this->inkMaterial();

        $supplier = Supplier::create([
            'name' => 'Inkwell Graphics Supply',
            'contact_person' => 'Maria Cruz',
            'phone' => '09171234567',
            'email' => 'sales@inkwell.test',
            'address' => 'Cebu City',
        ]);

        $item = SupplierMaterial::create([
            'supplier_id' => $supplier->id,
            'inventory_material_id' => $material->id,
            'supplier_unit_name' => $package,
            'conversion_qty' => $conversion,
            'unit_cost' => $unitCost,
        ]);

        return [$supplier, $item];
    }

    public function test_catalog_entry_derives_name_and_conversion_from_inventory(): void
    {
        $material = $this->inkMaterial();
        [, $item] = $this->supplierWithCatalog($material, 'Box', 5, 250.00);

        $this->assertSame('Sublimation Ink Black', $item->item_name);
        $this->assertSame('ml', $item->base_unit);
        $this->assertSame('1 Box = 5 ml', $item->package_label);
        $this->assertEquals(50.00, $item->base_unit_cost); // 250 per 5 ml box = 50 per ml
        $this->assertEquals(25.0, $item->toBaseUnits(5));  // 5 boxes = 25 ml
    }

    public function test_supplier_ledger_accessors_compute_payables(): void
    {
        [$supplier] = $this->supplierWithCatalog();

        Purchase::create([
            'supplier_id' => $supplier->id,
            'reference_no' => 'PO-TEST-0001',
            'total_amount' => 1000.00,
            'status' => Purchase::STATUS_RECEIVED,
        ]);

        // Cancelled POs must not inflate payables.
        Purchase::create([
            'supplier_id' => $supplier->id,
            'reference_no' => 'PO-TEST-0002',
            'total_amount' => 500.00,
            'status' => Purchase::STATUS_CANCELLED,
        ]);

        SupplierPayment::create([
            'supplier_id' => $supplier->id,
            'amount_paid' => 400.00,
            'payment_method' => 'GCash',
            'paid_at' => now()->toDateString(),
        ]);

        $supplier->refresh();

        $this->assertEquals(1000.00, $supplier->total_purchases);
        $this->assertEquals(400.00, $supplier->total_paid);
        $this->assertEquals(600.00, $supplier->balance);
    }

    public function test_purchase_order_creation_computes_subtotals_and_grand_total(): void
    {
        $material = $this->inkMaterial();
        [$supplier, $item] = $this->supplierWithCatalog($material, 'Gallon', 4, 1000.00);

        $response = $this->actingAs($this->admin())->post(route('admin.purchases.store'), [
            'supplier_id' => $supplier->id,
            'due_date' => now()->addWeek()->toDateString(),
            'notes' => 'Deliver to back entrance.',
            'items' => [
                ['supplier_material_id' => $item->id, 'quantity' => 3],
            ],
        ]);

        $purchase = Purchase::firstOrFail();

        $response->assertRedirect(route('admin.purchases.show', $purchase));

        $this->assertStringStartsWith('PO-', $purchase->reference_no);
        $this->assertEquals(3000.00, (float) $purchase->total_amount);
        $this->assertSame(Purchase::STATUS_PENDING, $purchase->status);

        $line = $purchase->items()->firstOrFail();
        // Line names are snapshotted from the linked inventory material.
        $this->assertSame('Sublimation Ink Black', $line->item_name);
        $this->assertSame(3, $line->quantity);
        $this->assertEquals(1000.00, (float) $line->unit_cost);
        $this->assertEquals(3000.00, (float) $line->subtotal);
    }

    public function test_purchase_order_rejects_catalog_items_from_another_supplier(): void
    {
        [$supplierA] = $this->supplierWithCatalog($this->inkMaterial());
        [, $itemB] = $this->supplierWithCatalog($this->inkMaterial(stock: 50));

        $response = $this->actingAs($this->admin())->post(route('admin.purchases.store'), [
            'supplier_id' => $supplierA->id,
            'items' => [
                ['supplier_material_id' => $itemB->id, 'quantity' => 1],
            ],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertSame(0, Purchase::count());
    }

    public function test_receiving_a_purchase_order_converts_packages_into_base_units(): void
    {
        $material = $this->inkMaterial(stock: 100);
        [$supplier, $item] = $this->supplierWithCatalog($material, 'Box', 5, 250.00);

        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'reference_no' => 'PO-RESTOCK-0001',
            'total_amount' => 1250.00,
            'status' => Purchase::STATUS_PENDING,
        ]);

        $purchase->items()->create([
            'supplier_material_id' => $item->id,
            'item_name' => $item->item_name,
            'quantity' => 5, // 5 boxes ordered
            'unit_cost' => 250.00,
            'subtotal' => 1250.00,
        ]);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.purchases.status', $purchase), ['status' => 'received'])
            ->assertSessionHas('success');

        $this->assertSame(Purchase::STATUS_RECEIVED, $purchase->fresh()->status);
        // 5 boxes x 5 ml per box = 25 ml added on top of 100 ml.
        $this->assertEquals(125.0, (float) $material->fresh()->current_stock);

        $log = MaterialLog::where('material_id', $material->id)->firstOrFail();
        $this->assertSame('restock', $log->type);
        $this->assertEquals(-25, (float) $log->quantity_deducted);
        $this->assertEquals(100, (float) $log->stock_before);
        $this->assertEquals(125, (float) $log->stock_after);
        $this->assertStringContainsString('PO-RESTOCK-0001', $log->notes);
        $this->assertStringContainsString('1 Box = 5 ml', $log->notes);

        // Receiving twice must not double the stock.
        $this->actingAs($admin)
            ->post(route('admin.purchases.status', $purchase), ['status' => 'received']);

        $this->assertEquals(125.0, (float) $material->fresh()->current_stock);
        $this->assertSame(1, MaterialLog::where('material_id', $material->id)->count());
    }

    public function test_catalog_requires_an_inventory_material_and_offers_extra_package_sizes(): void
    {
        $supplier = Supplier::create(['name' => 'South Print Depot']);
        $material = $this->inkMaterial();
        $admin = $this->admin();

        // No inventory material selected -> rejected.
        $this->actingAs($admin)->post(route('admin.suppliers.catalog.store', $supplier), [
            'supplier_unit_name' => 'Box',
            'conversion_qty' => 5,
            'unit_cost' => 250,
        ])->assertSessionHasErrors('inventory_material_id');

        $this->actingAs($admin)->post(route('admin.suppliers.catalog.store', $supplier), [
            'inventory_material_id' => $material->id,
            'supplier_unit_name' => 'Box',
            'conversion_qty' => 5,
            'unit_cost' => 250,
        ])->assertSessionHas('success');

        $entry = SupplierMaterial::where('supplier_unit_name', 'Box')->firstOrFail();
        $this->assertSame($material->id, $entry->inventory_material_id);
        $this->assertEquals(5.0, (float) $entry->conversion_qty);

        // The same item sold in another package size is a legitimate second entry,
        // so catalogued items must never disappear from the dropdown.
        $this->actingAs($admin)->post(route('admin.suppliers.catalog.store', $supplier), [
            'inventory_material_id' => $material->id,
            'supplier_unit_name' => 'Gallon',
            'conversion_qty' => 4,
            'unit_cost' => 200,
        ])->assertSessionHas('success');

        $this->assertSame(2, SupplierMaterial::count());

        // Only the exact same package for the same item is a duplicate (case-insensitive).
        $this->actingAs($admin)->post(route('admin.suppliers.catalog.store', $supplier), [
            'inventory_material_id' => $material->id,
            'supplier_unit_name' => 'box',
            'conversion_qty' => 9,
            'unit_cost' => 100,
        ])->assertSessionHasErrors('supplier_unit_name');

        $this->assertSame(2, SupplierMaterial::count());
    }

    public function test_supplier_and_purchase_pages_render(): void
    {
        $liquid = $this->inkMaterial();
        Material::create([
            'name' => 'Matte Sticker Paper A4',
            'category' => 'Paper',
            'unit' => 'sheets',
            'current_stock' => 500,
            'low_stock_threshold' => 50,
        ]);

        [$supplier, $item] = $this->supplierWithCatalog($liquid);

        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'reference_no' => 'PO-VIEW-0001',
            'total_amount' => 500.00,
            'status' => Purchase::STATUS_PENDING,
        ]);

        $purchase->items()->create([
            'supplier_material_id' => $item->id,
            'item_name' => $item->item_name,
            'quantity' => 2,
            'unit_cost' => 250.00,
            'subtotal' => 500.00,
        ]);

        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.suppliers.index'))->assertOk();

        $ledger = $this->actingAs($admin)->get(route('admin.suppliers.show', $supplier));
        $ledger->assertOk();
        $ledger->assertSee('1 Box = 5 ml');
        // The catalog is wired to real inventory: every stock item is offered, with its live level.
        $ledger->assertSee('Sublimation Ink Black');
        $ledger->assertSee('Matte Sticker Paper A4');
        $ledger->assertSee('stock 500 sheets');
        $ledger->assertSee('Linked inventory stock');
        $ledger->assertSee('low_stock_threshold', false); // stock data reaches Alpine for the live preview
        // An item already in the catalog stays selectable instead of vanishing.
        $ledger->assertSee('· in catalog');
        $ledger->assertSee('catalogued_as', false);

        $this->actingAs($admin)->get(route('admin.purchases.index'))->assertOk();

        $form = $this->actingAs($admin)->get(route('admin.purchases.create'));
        $form->assertOk();
        $form->assertSee('package_label', false); // the catalog JSON exposes conversions to Alpine
        $form->assertSee('1 Box = 5 ml');

        $this->actingAs($admin)->get(route('admin.purchases.show', $purchase))->assertOk();
    }

    public function test_supplier_can_be_created_with_catalog_and_payment_via_http(): void
    {
        $material = $this->inkMaterial();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.suppliers.store'), [
            'name' => 'South Print Depot',
            'contact_person' => 'Juan Dela Cruz',
            'phone' => '09180001111',
            'email' => 'hello@southprint.test',
            'address' => 'Davao City',
        ])->assertRedirect();

        $supplier = Supplier::where('name', 'South Print Depot')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.suppliers.catalog.store', $supplier), [
            'inventory_material_id' => $material->id,
            'supplier_unit_name' => 'Gallon',
            'conversion_qty' => 3.785,
            'unit_cost' => 950,
        ])->assertSessionHas('success');

        $this->assertSame(1, $supplier->materials()->count());

        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'reference_no' => 'PO-PAY-0001',
            'total_amount' => 200.00,
            'status' => Purchase::STATUS_PENDING,
        ]);

        $this->actingAs($admin)->post(route('admin.suppliers.payments.store', $supplier), [
            'purchase_id' => $purchase->id,
            'amount_paid' => 120.00,
            'payment_method' => 'Bank Transfer',
            'reference_no' => 'BT-9988',
        ])->assertSessionHas('success');

        $payment = SupplierPayment::firstOrFail();
        $this->assertSame($supplier->id, $payment->supplier_id);
        $this->assertEquals(120.00, (float) $payment->amount_paid);
        $this->assertNotNull($payment->paid_at);

        $this->assertEquals(200.00, $supplier->fresh()->total_purchases);
        $this->assertEquals(120.00, $supplier->fresh()->total_paid);
        $this->assertEquals(80.00, $supplier->fresh()->balance);
    }

    public function test_suppliers_can_be_exported_to_excel_xlsx(): void
    {
        Supplier::create([
            'name' => 'South Print Depot',
            'contact_person' => 'Juan Dela Cruz',
            'phone' => '09180001111',
            'email' => 'hello@southprint.test',
            'address' => 'Davao City',
        ]);

        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('admin.suppliers.export'));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));

        $content = $response->getContent();
        // A valid XLSX file starts with PK (standard ZIP header magic bytes \x50\x4B\x03\x04)
        $this->assertStringStartsWith("PK", $content);

        // Verify it can be opened as a ZipArchive
        $temp = tempnam(sys_get_temp_dir(), 'test_zip_');
        file_put_contents($temp, $content);
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($temp));
        $this->assertNotFalse($zip->locateName('xl/worksheets/sheet1.xml'));
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertStringContainsString('South Print Depot', $sheetXml);
        $this->assertStringContainsString('09180001111', $sheetXml);
        $zip->close();
        @unlink($temp);
    }
}
