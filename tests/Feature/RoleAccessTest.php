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
        $response->assertSee('Masuk ke Sistem');
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

    public function test_sidebar_unified_menu_for_admin_and_kasir(): void
    {
        $admin = User::create([
            'name' => 'Admin Toko',
            'email' => 'admin_sidebar@toko.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $kasir = User::create([
            'name' => 'Kasir Toko',
            'email' => 'kasir_sidebar@toko.com',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        // Admin sees unified "Inventaris & Stok" menu with both submenus
        $responseAdmin = $this->actingAs($admin)->get('/dashboard');
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Inventaris & Stok', false);
        $responseAdmin->assertSee('Master Data Barang');
        $responseAdmin->assertSee('Catat Stok Masuk');

        // Kasir only sees "Catat Stok Masuk", does NOT see "Inventaris & Stok" dropdown or "Master Data Barang"
        $responseKasir = $this->actingAs($kasir)->get('/stok');
        $responseKasir->assertStatus(200);
        $responseKasir->assertSee('Catat Stok Masuk');
        $responseKasir->assertDontSee('Inventaris & Stok', false);
        $responseKasir->assertDontSee('Master Data Barang');
    }

    public function test_dashboard_period_filters_sales_correctly(): void
    {
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'adm_period@toko.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create transaction 2 days ago (this month, but not today)
        $t1 = \App\Models\Transaction::create([
            'invoice_number' => 'INV-TEST-001',
            'customer_name' => 'Umum',
            'cashier_name' => 'Kasir Test',
            'total_amount' => 150000,
            'paid_amount' => 150000,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'success',
        ]);
        $t1->created_at = \Carbon\Carbon::now()->subDays(2);
        $t1->save();

        // Create transaction today
        \App\Models\Transaction::create([
            'invoice_number' => 'INV-TEST-002',
            'customer_name' => 'Umum',
            'cashier_name' => 'Kasir Test',
            'total_amount' => 50000,
            'paid_amount' => 50000,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'success',
        ]);

        // When period is today: only 50.000
        $resToday = $this->actingAs($admin)->get('/dashboard?period=today');
        $resToday->assertStatus(200);
        $resToday->assertSee('50.000');
        $resToday->assertSee('transaksi (hari ini)');

        // When period is month: 150.000 + 50.000 = 200.000
        $resMonth = $this->actingAs($admin)->get('/dashboard?period=month');
        $resMonth->assertStatus(200);
        $resMonth->assertSee('200.000');
        $resMonth->assertSee('transaksi (bulan ini)');
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
