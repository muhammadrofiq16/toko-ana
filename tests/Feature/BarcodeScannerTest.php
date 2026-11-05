<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BarcodeScannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_barcode_endpoint(): void
    {
        $response = $this->getJson('/barang/barcode/123456789');
        $response->assertStatus(401);
    }

    public function test_user_can_find_product_by_barcode(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);

        $product = Product::create([
            'sku' => 'BRG-001',
            'barcode' => '899123456789',
            'name' => 'Indomie Goreng Rasa Spesial',
            'category' => 'Makanan',
            'stock' => 50,
            'min_stock' => 10,
            'unit' => 'Bungkus',
            'cost_price' => 2500,
        ]);

        $response = $this->actingAs($user)->getJson('/barang/barcode/899123456789');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => 'Indomie Goreng Rasa Spesial',
                    'barcode' => '899123456789',
                    'sku' => 'BRG-001',
                ]
            ]);
    }

    public function test_user_can_find_product_by_sku(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $product = Product::create([
            'sku' => 'BRG-099',
            'barcode' => null,
            'name' => 'Kecap Manis Botol 600ml',
            'category' => 'Sembako',
            'stock' => 20,
            'min_stock' => 5,
            'unit' => 'Botol',
            'cost_price' => 15000,
        ]);

        $response = $this->actingAs($user)->getJson('/barang/barcode/BRG-099');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'sku' => 'BRG-099',
                ]
            ]);
    }

    public function test_non_existent_barcode_returns_404(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);

        $response = $this->actingAs($user)->getJson('/barang/barcode/999999999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_kasir_view_contains_barcode_scanner_elements(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $response = $this->actingAs($kasir)->get('/kasir');

        $response->assertStatus(200);
        $response->assertSee('SCAN (F2)');
        $response->assertSee('Scan Kamera');
        $response->assertSee('interactive-kasir-scanner');
    }

    public function test_stok_view_contains_barcode_scanner_elements(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/stok');

        $response->assertStatus(200);
        $response->assertSee('Barcode Gun Aktif');
        $response->assertSee('Scan Kamera (F2)');
        $response->assertSee('interactive-stok-scanner');
    }
}
