<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. GCash, Maya, Bank Transfer, InstaPay, Cash on Pickup
            $table->string('type')->default('manual'); // 'manual' or 'gateway'
            $table->string('account_name')->nullable(); // e.g. Grafika South Print Shop
            $table->string('account_number')->nullable(); // e.g. 0917-000-0001
            $table->string('qr_code_path')->nullable(); // Uploaded QR Code image path
            $table->text('instructions')->nullable(); // Instructions for customer
            $table->text('api_keys')->nullable(); // Optional API keys for future gateway integration
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default payment methods
        DB::table('payment_methods')->insert([
            [
                'name' => 'GCash',
                'type' => 'manual',
                'account_name' => 'Grafika South Print Shop',
                'account_number' => '0917-000-0001',
                'instructions' => 'Send payment via GCash app, save/screenshot your official receipt, and upload the proof of payment.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Maya / PayMaya',
                'type' => 'manual',
                'account_name' => 'Grafika South Print Shop',
                'account_number' => '0917-000-0001',
                'instructions' => 'Send payment via Maya app or scan QR, save receipt screenshot, and attach it to your order.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bank Transfer (BDO / BPI / InstaPay)',
                'type' => 'manual',
                'account_name' => 'Grafika South Commercial Printing',
                'account_number' => 'BDO: 0012-3456-7890 | BPI: 1234-5678-90',
                'instructions' => 'Transfer exact order amount via Online Banking or InstaPay and upload your transfer confirmation receipt screenshot.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cash on Pickup / Store',
                'type' => 'manual',
                'account_name' => 'Grafika South San Pedro Store',
                'account_number' => 'Pay upon store collection',
                'instructions' => 'Pay cash in person at our San Pedro, Laguna print shop branch upon order pickup.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
