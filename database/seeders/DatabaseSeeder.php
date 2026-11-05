<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\OrderItem;
use App\Models\OrderMessage;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductMaterial;
use App\Models\ProductOption;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Default Payment Methods
        PaymentMethod::firstOrCreate(
            ['name' => 'Cash / Store Pickup'],
            [
                'type' => 'manual',
                'account_name' => 'Store Counter',
                'instructions' => 'Pay in cash upon store collection at our San Pedro, Laguna branch. No payment screenshot required.',
                'is_online_payment' => false,
                'is_active' => true,
            ]
        );
        // 1. Core Users
        $superAdmin = User::updateOrCreate(
            ['email' => 'grafikasouth69@gmail.com'],
            [
                'name' => 'Super Administrator',
                'password' => 'password123',
                'role' => 'super_admin',
                'phone' => '09170000001',
                'contact' => '09170000001',
                'address' => 'Grafika HQ, San Pedro, Laguna',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@grafika.com'],
            [
                'name' => 'Grafika Staff Admin',
                'password' => 'password123',
                'role' => 'admin',
                'phone' => '09170000002',
                'contact' => '09170000002',
                'address' => 'Grafika Branch, San Pedro, Laguna',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $customer = User::updateOrCreate(
            ['email' => 'customer@grafika.com'],
            [
                'name' => 'Juan Dela Cruz',
                'password' => 'password123',
                'role' => 'customer',
                'phone' => '09171234567',
                'contact' => '09171234567',
                'address' => '123 Rizal St., Brgy. Poblacion, San Pedro, Laguna',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // 2. Raw Materials
        $ink = Material::updateOrCreate(
            ['name' => 'Sublimation Ink (CMYK Pack)'],
            [
                'category' => 'Ink',
                'unit' => 'ml',
                'current_stock' => 250.00,
                'low_stock_threshold' => 50.00,
            ]
        );

        $blankShirt = Material::updateOrCreate(
            ['name' => 'Blank Cotton T-Shirt (White)'],
            [
                'category' => 'Garment',
                'unit' => 'pcs',
                'current_stock' => 120.00,
                'low_stock_threshold' => 20.00,
            ]
        );

        $transferSheet = Material::updateOrCreate(
            ['name' => 'A4 Heat Transfer Paper'],
            [
                'category' => 'Paper',
                'unit' => 'sheets',
                'current_stock' => 180.00,
                'low_stock_threshold' => 30.00,
            ]
        );

        $pvcSheet = Material::updateOrCreate(
            ['name' => 'PVC Card Blank Sheet'],
            [
                'category' => 'Cardstock',
                'unit' => 'pcs',
                'current_stock' => 350.00,
                'low_stock_threshold' => 40.00,
            ]
        );

        $cardstock = Material::updateOrCreate(
            ['name' => '300gsm Glossy Cardstock (Box)'],
            [
                'category' => 'Paper',
                'unit' => 'sheets',
                'current_stock' => 800.00,
                'low_stock_threshold' => 100.00,
            ]
        );

        $laminationFilm = Material::updateOrCreate(
            ['name' => 'A4 Thermal Lamination Pouch'],
            [
                'category' => 'Film',
                'unit' => 'sheets',
                'current_stock' => 300.00,
                'low_stock_threshold' => 50.00,
            ]
        );

        // 3. Products Offered
        // Product 1: T-shirt printing
        $tshirt = Product::updateOrCreate(
            ['slug' => 'custom-tshirt-printing'],
            [
                'name' => 'Custom T-Shirt Printing',
                'category' => 'Apparel',
                'description' => 'High-definition heat transfer sublimation print on premium cotton T-shirts. Fully customizable with logo or full graphic.',
                'price' => 250.00,
                'base_price' => 250.00,
                'is_active' => true,
            ]
        );

        ProductOption::where('product_id', $tshirt->id)->delete();
        ProductOption::create(['product_id' => $tshirt->id, 'option_name' => 'Size', 'option_value' => 'Small', 'price_modifier' => 0.00]);
        ProductOption::create(['product_id' => $tshirt->id, 'option_name' => 'Size', 'option_value' => 'Medium', 'price_modifier' => 0.00]);
        ProductOption::create(['product_id' => $tshirt->id, 'option_name' => 'Size', 'option_value' => 'Large', 'price_modifier' => 20.00]);
        ProductOption::create(['product_id' => $tshirt->id, 'option_name' => 'Size', 'option_value' => 'X-Large', 'price_modifier' => 40.00]);
        ProductOption::create(['product_id' => $tshirt->id, 'option_name' => 'Color', 'option_value' => 'White', 'price_modifier' => 0.00]);
        ProductOption::create(['product_id' => $tshirt->id, 'option_name' => 'Color', 'option_value' => 'Black', 'price_modifier' => 30.00]);

        ProductMaterial::where('product_id', $tshirt->id)->delete();
        ProductMaterial::create(['product_id' => $tshirt->id, 'material_id' => $blankShirt->id, 'quantity_used' => 1.0000]);
        ProductMaterial::create(['product_id' => $tshirt->id, 'material_id' => $ink->id, 'quantity_used' => 5.0000]); // 5ml ink per shirt
        ProductMaterial::create(['product_id' => $tshirt->id, 'material_id' => $transferSheet->id, 'quantity_used' => 1.0000]);

        // Product 2: ID printing
        $idCard = Product::updateOrCreate(
            ['slug' => 'pvc-id-card-printing'],
            [
                'name' => 'PVC ID Card Printing',
                'category' => 'Identification',
                'description' => 'Durable 30mil waterproof PVC ID cards with vibrant color print and slot punch for company, school, or event access.',
                'price' => 50.00,
                'base_price' => 50.00,
                'is_active' => true,
            ]
        );

        ProductOption::where('product_id', $idCard->id)->delete();
        ProductOption::create(['product_id' => $idCard->id, 'option_name' => 'Print Side', 'option_value' => 'Single-Sided', 'price_modifier' => 0.00]);
        ProductOption::create(['product_id' => $idCard->id, 'option_name' => 'Print Side', 'option_value' => 'Double-Sided', 'price_modifier' => 20.00]);
        ProductOption::create(['product_id' => $idCard->id, 'option_name' => 'Lanyard', 'option_value' => 'No Lanyard', 'price_modifier' => 0.00]);
        ProductOption::create(['product_id' => $idCard->id, 'option_name' => 'Lanyard', 'option_value' => 'Standard Black Lanyard', 'price_modifier' => 25.00]);

        ProductMaterial::where('product_id', $idCard->id)->delete();
        ProductMaterial::create(['product_id' => $idCard->id, 'material_id' => $pvcSheet->id, 'quantity_used' => 1.0000]);
        ProductMaterial::create(['product_id' => $idCard->id, 'material_id' => $ink->id, 'quantity_used' => 1.5000]); // 1.5ml ink per ID

        // Product 3: Business card printing
        $bizCard = Product::updateOrCreate(
            ['slug' => 'business-card-printing-pack'],
            [
                'name' => 'Business Cards (Pack of 100)',
                'category' => 'Marketing',
                'description' => 'Standard 3.5" x 2" business cards on heavy 300gsm glossy cardstock with optional lamination finish.',
                'price' => 350.00,
                'base_price' => 350.00,
                'is_active' => true,
            ]
        );

        ProductOption::where('product_id', $bizCard->id)->delete();
        ProductOption::create(['product_id' => $bizCard->id, 'option_name' => 'Finish', 'option_value' => 'Glossy Coating', 'price_modifier' => 0.00]);
        ProductOption::create(['product_id' => $bizCard->id, 'option_name' => 'Finish', 'option_value' => 'Matte Soft-Touch', 'price_modifier' => 30.00]);
        ProductOption::create(['product_id' => $bizCard->id, 'option_name' => 'Corner', 'option_value' => 'Standard Square', 'price_modifier' => 0.00]);
        ProductOption::create(['product_id' => $bizCard->id, 'option_name' => 'Corner', 'option_value' => 'Rounded Corners', 'price_modifier' => 25.00]);

        ProductMaterial::where('product_id', $bizCard->id)->delete();
        ProductMaterial::create(['product_id' => $bizCard->id, 'material_id' => $cardstock->id, 'quantity_used' => 10.0000]); // 10 sheets per 100 cards
        ProductMaterial::create(['product_id' => $bizCard->id, 'material_id' => $ink->id, 'quantity_used' => 4.0000]); // 4ml ink per pack

        // Product 4: Lamination services
        $lamination = Product::updateOrCreate(
            ['slug' => 'document-lamination-services'],
            [
                'name' => 'Thermal Lamination Service',
                'category' => 'Services',
                'description' => 'Waterproof sealed document lamination for certificates, notices, ID cards, and menus.',
                'price' => 25.00,
                'base_price' => 25.00,
                'is_active' => true,
            ]
        );

        ProductOption::where('product_id', $lamination->id)->delete();
        ProductOption::create(['product_id' => $lamination->id, 'option_name' => 'Size', 'option_value' => 'A4 Document', 'price_modifier' => 0.00]);
        ProductOption::create(['product_id' => $lamination->id, 'option_name' => 'Size', 'option_value' => 'A3 Poster Size', 'price_modifier' => 25.00]);
        ProductOption::create(['product_id' => $lamination->id, 'option_name' => 'Thickness', 'option_value' => '125 Micron Standard', 'price_modifier' => 0.00]);
        ProductOption::create(['product_id' => $lamination->id, 'option_name' => 'Thickness', 'option_value' => '250 Micron Extra Rigid', 'price_modifier' => 15.00]);

        ProductMaterial::where('product_id', $lamination->id)->delete();
        ProductMaterial::create(['product_id' => $lamination->id, 'material_id' => $laminationFilm->id, 'quantity_used' => 1.0000]);

        // 4. Sample Demonstration Order
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'user_id' => $customer->id,
            'status' => 'design_review',
            'payment_status' => 'paid',
            'total_price' => 270.00,
            'total_amount' => 270.00,
            'notes' => 'Please make sure logo is centered on chest.',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $tshirt->id,
            'quantity' => 1,
            'unit_price' => 270.00,
            'subtotal' => 270.00,
            'options_selected' => [
                'Size' => 'Large (+₱20.00)',
                'Color' => 'White',
            ],
        ]);

        OrderFile::create([
            'order_item_id' => $orderItem->id,
            'file_path' => 'demo_artwork.png',
            'uploaded_by' => $customer->id,
            'status' => 'needs_revision',
            'admin_reply' => 'The logo uploaded is low resolution (72dpi). Please upload a vector (SVG/PDF) or high-res 300dpi file so print output stays crisp.',
        ]);

        OrderMessage::create([
            'order_id' => $order->id,
            'user_id' => $admin->id,
            'message' => 'Hello Juan! We noticed your artwork resolution is a bit low. Please check our note in the design review section.',
        ]);

        OrderMessage::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'message' => 'Thanks for letting me know! I will upload the vector PDF version shortly.',
        ]);
    }
}
