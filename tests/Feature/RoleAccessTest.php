<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        $responseKasir = $this->get('/kasir');
        $responseKasir->assertRedirect('/login');

        $responseStok = $this->get('/stok');
        $responseStok->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SIKOL POS');
        $response->assertSee('admin@toko.com');
        $response->assertSee('kasir@toko.com');
    }

    public function test_kasir_login_and_redirection(): void
    {
        $kasir = User::create([
            'name' => 'Siti Kasir',
            'email' => 'siti@toko.com',
            'password' => bcrypt('password123'),
            'role' => 'kasir',
        ]);

        $this->assertTrue($kasir->isKasir());
        $this->assertFalse($kasir->isAdmin());

        $response = $this->post('/login', [
            'email' => 'siti@toko.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/kasir');
        $this->assertAuthenticatedAs($kasir);
    }

    public function test_admin_login_and_redirection(): void
    {
        $admin = User::create([
            'name' => 'Budi Pemilik',
            'email' => 'budi@toko.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isKasir());

        $response = $this->post('/login', [
            'email' => 'budi@toko.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_kasir_can_access_allowed_pages(): void
    {
        $kasir = User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir@toko.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        // Kasir POS
        $response = $this->actingAs($kasir)->get('/kasir');
        $response->assertStatus(200);

        // Catat Stok Masuk
        $response = $this->actingAs($kasir)->get('/stok');
        $response->assertStatus(200);

        // Dashboard (Kasir allowed)
        $response = $this->actingAs($kasir)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Akses Kasir');
        $response->assertDontSee('Estimasi nilai persediaan');
    }

    public function test_kasir_blocked_from_admin_pages(): void
    {
        $kasir = User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir@toko.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        // Attempting to access Master Data Barang
        $responseBarang = $this->actingAs($kasir)->get('/barang');
        $responseBarang->assertRedirect('/kasir');
        $responseBarang->assertSessionHas('error');

        // Attempting to access Riwayat Transaksi & Laporan
        $responseTransaksi = $this->actingAs($kasir)->get('/transaksi');
        $responseTransaksi->assertRedirect('/kasir');
        $responseTransaksi->assertSessionHas('error');

        // Attempting to access Pengaturan
        $responsePengaturan = $this->actingAs($kasir)->get('/pengaturan');
        $responsePengaturan->assertRedirect('/kasir');
        $responsePengaturan->assertSessionHas('error');
    }

    public function test_admin_can_access_admin_pages(): void
    {
        $admin = User::create([
            'name' => 'Admin Toko',
            'email' => 'admin@toko.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Dashboard (with inventory value)
        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Estimasi nilai persediaan');

        // Master Data Barang
        $response = $this->actingAs($admin)->get('/barang');
        $response->assertStatus(200);

        // Transaksi
        $response = $this->actingAs($admin)->get('/transaksi');
        $response->assertStatus(200);

        // Pengaturan
        $response = $this->actingAs($admin)->get('/pengaturan');
        $response->assertStatus(200);
    }

    public function test_logout_redirects_to_login(): void
    {
        $user = User::create([
            'name' => 'User',
            'email' => 'user@toko.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
