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
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // Seed default settings
        $defaults = [
            ['key' => 'store_name', 'value' => 'Toko Berkah Kelontong'],
            ['key' => 'store_status', 'value' => 'Buka (Operasional Normal)'],
            ['key' => 'cashier_name', 'value' => 'Budi Santoso'],
            ['key' => 'shift', 'value' => 'Kasir Shift 1 (Pagi)'],
            ['key' => 'work_shift', 'value' => 'Kasir Shift 1 (Pagi)'],
            ['key' => 'store_address', 'value' => 'Jl. Contoh Alamat No. 123, Kota'],
            ['key' => 'store_phone', 'value' => '021-12345678'],
        ];

        foreach ($defaults as $item) {
            if (!DB::table('settings')->where('key', $item['key'])->exists()) {
                DB::table('settings')->insert(array_merge($item, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
