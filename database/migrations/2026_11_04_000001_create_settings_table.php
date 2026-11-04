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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default store configurations
        DB::table('settings')->insert([
            ['key' => 'store_name', 'value' => 'Toko Berkah Kelontong', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'store_status', 'value' => 'Buka (Operasional Normal)', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'cashier_name', 'value' => 'Budi Santoso', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'work_shift', 'value' => 'Kasir Shift 1 (Pagi)', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'store_address', 'value' => 'Jl. Contoh Alamat No. 123, Kota', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'store_phone', 'value' => '021-12345678', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
