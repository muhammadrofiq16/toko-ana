<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Setup in-memory sqlite connection for testing
$config = $app->make('config');
$config->set('database.default', 'sqlite');
$config->set('database.connections.sqlite', [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);

// Run users migrations
Illuminate\Support\Facades\Artisan::call('migrate', [
    '--path' => [
        'database/migrations/2014_10_12_000000_create_users_table.php',
        'database/migrations/2026_11_04_000001_add_role_to_users_table.php',
    ],
]);

echo "========================================\n";
echo " TESTING USER MANAGEMENT CONTROLLER     \n";
echo "========================================\n\n";

use App\Models\User;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

$controller = new UserController();

// 1. Create Initial Admin
$admin1 = User::create([
    'name' => 'Admin Utama',
    'email' => 'admin@toko.com',
    'password' => Hash::make('password123'),
    'role' => 'admin',
]);
Auth::setUser($admin1);
echo " [PASS] Created Initial Admin\n";

// 2. Store: Add new Kasir
$reqStore = Request::create('/pengguna', 'POST', [
    'name' => 'Rina Kasir',
    'email' => 'rina@toko.com',
    'role' => 'kasir',
    'password' => 'password123',
]);
$respStore = $controller->store($reqStore);
$createdUser = User::where('email', 'rina@toko.com')->first();
if ($createdUser && $createdUser->role === 'kasir') {
    echo " [PASS] store(): Berhasil menambahkan kasir baru (Rina Kasir, role: kasir)\n";
} else {
    echo " [FAIL] store(): Gagal menambahkan kasir baru\n";
    exit(1);
}

// 3. Update: Ubah Role Kasir -> Admin
$reqUpdateRole = Request::create("/pengguna/{$createdUser->id}", 'PUT', [
    'name' => 'Rina Admin',
    'email' => 'rina@toko.com',
    'role' => 'admin',
]);
$controller->update($reqUpdateRole, $createdUser);
$createdUser->refresh();
if ($createdUser->role === 'admin' && $createdUser->name === 'Rina Admin') {
    echo " [PASS] update(): Berhasil mengubah role kasir menjadi admin (Promosi role)\n";
} else {
    echo " [FAIL] update(): Gagal mengubah role\n";
    exit(1);
}

// 4. Update: Ubah Role Admin -> Kasir kembali
$reqDemote = Request::create("/pengguna/{$createdUser->id}", 'PUT', [
    'name' => 'Rina Kasir',
    'email' => 'rina@toko.com',
    'role' => 'kasir',
]);
$controller->update($reqDemote, $createdUser);
$createdUser->refresh();
if ($createdUser->role === 'kasir') {
    echo " [PASS] update(): Berhasil mengubah role admin menjadi kasir kembali\n";
} else {
    echo " [FAIL] update(): Gagal demote role\n";
    exit(1);
}

// 5. Security: Admin mencoba menghapus akun dirinya sendiri yang sedang login
$respSelfDelete = $controller->destroy($admin1);
if (session('error') && User::find($admin1->id)) {
    echo " [PASS] destroy(): Dicegah menghapus akun sendiri yang sedang aktif digunakan\n";
} else {
    echo " [FAIL] destroy(): Akun sendiri tidak dicegah\n";
    exit(1);
}

// 6. Security: Mencoba mengubah role admin terakhir menjadi kasir
$reqDemoteLastAdmin = Request::create("/pengguna/{$admin1->id}", 'PUT', [
    'name' => 'Admin Utama',
    'email' => 'admin@toko.com',
    'role' => 'kasir',
]);
$controller->update($reqDemoteLastAdmin, $admin1);
$admin1->refresh();
if ($admin1->role === 'admin') {
    echo " [PASS] update(): Dicegah mengubah role admin terakhir menjadi kasir\n";
} else {
    echo " [FAIL] update(): Admin terakhir berhasil diubah menjadi kasir\n";
    exit(1);
}

// 7. Destroy: Menghapus akun kasir
$controller->destroy($createdUser);
if (!User::find($createdUser->id)) {
    echo " [PASS] destroy(): Berhasil menghapus akun kasir\n";
} else {
    echo " [FAIL] destroy(): Gagal menghapus akun kasir\n";
    exit(1);
}

echo "\n========================================\n";
echo " ALL USER CONTROLLER TESTS PASSED!      \n";
echo "========================================\n";
