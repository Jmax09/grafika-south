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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->boolean('is_online_payment')->default(true)->after('api_keys');
        });

        // Backfill existing payment methods
        $methods = DB::table('payment_methods')->get();
        foreach ($methods as $method) {
            $name = strtolower($method->name);
            $isOffline = str_contains($name, 'cash on delivery')
                || str_contains($name, 'cash on pickup')
                || str_contains($name, 'cash on pick-up')
                || str_contains($name, 'cash on store')
                || str_contains($name, 'pay at print shop')
                || str_contains($name, 'pay upon store collection')
                || str_contains($name, 'cod')
                || str_contains($name, 'cop')
                || (trim($name) === 'cash');

            DB::table('payment_methods')->where('id', $method->id)->update([
                'is_online_payment' => !$isOffline,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('is_online_payment');
        });
    }
};