<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            
            $table->boolean('is_walkin')->default(false)->after('user_id');
            $table->string('walkin_first_name')->nullable()->after('is_walkin');
            $table->string('walkin_last_name')->nullable()->after('walkin_first_name');
            $table->string('walkin_email')->nullable()->after('walkin_last_name');
            $table->string('walkin_contact_number')->nullable()->after('walkin_email');
            $table->text('walkin_address')->nullable()->after('walkin_contact_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            
            $table->dropColumn([
                'is_walkin',
                'walkin_first_name',
                'walkin_last_name',
                'walkin_email',
                'walkin_contact_number',
                'walkin_address'
            ]);
        });
    }
};
