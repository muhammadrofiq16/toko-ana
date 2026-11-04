<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "========================================\n";
echo " TESTING ROLE & AUTHENTICATION SYSTEM   \n";
echo "========================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertCondition($condition, $description) {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo " [PASS] $description\n";
        $testsPassed++;
    } else {
        echo " [FAIL] $description\n";
        $testsFailed++;
    }
}

// 1. Test User Model helpers
$kasirUser = new App\Models\User(['name' => 'Siti', 'email' => 'siti@toko.com', 'role' => 'kasir']);
assertCondition($kasirUser->isKasir() === true, "User model: isKasir() returns true for kasir");
assertCondition($kasirUser->isAdmin() === false, "User model: isAdmin() returns false for kasir");

$adminUser = new App\Models\User(['name' => 'Budi', 'email' => 'budi@toko.com', 'role' => 'admin']);
assertCondition($adminUser->isAdmin() === true, "User model: isAdmin() returns true for admin");
assertCondition($adminUser->isKasir() === false, "User model: isKasir() returns false for admin");

// Helper to simulate HTTP requests through Kernel
function simulateRequest($method, $uri, $user = null, $data = []) {
    global $kernel, $app;
    
    $request = Illuminate\Http\Request::create($uri, $method, $data);
    $app->instance('request', $request);
    
    // Reset session / auth state
    if (Illuminate\Support\Facades\Auth::hasResolvedGuards()) {
        Illuminate\Support\Facades\Auth::forgetGuards();
    }
    if ($user) {
        Illuminate\Support\Facades\Auth::guard('web')->setUser($user);
    }
    
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    
    return $response;
}

// 2. Test Guest Redirection
$respGuestDash = simulateRequest('GET', '/dashboard');
assertCondition($respGuestDash->isRedirection() && str_contains($respGuestDash->headers->get('Location'), 'login'), 
    "Guest visiting /dashboard is redirected to /login (Status: {$respGuestDash->getStatusCode()})");

$respGuestKasir = simulateRequest('GET', '/kasir');
assertCondition($respGuestKasir->isRedirection() && str_contains($respGuestKasir->headers->get('Location'), 'login'), 
    "Guest visiting /kasir is redirected to /login (Status: {$respGuestKasir->getStatusCode()})");

$respGuestStok = simulateRequest('GET', '/stok');
assertCondition($respGuestStok->isRedirection() && str_contains($respGuestStok->headers->get('Location'), 'login'), 
    "Guest visiting /stok is redirected to /login (Status: {$respGuestStok->getStatusCode()})");

// 3. Test Login Page renders
$respLogin = simulateRequest('GET', '/login');
$loginContent = $respLogin->getContent();
assertCondition($respLogin->getStatusCode() === 200, "Login page returns 200 OK");
assertCondition(str_contains($loginContent, 'SIKOL POS'), "Login page contains branding 'SIKOL POS'");
assertCondition(str_contains($loginContent, 'admin@toko.com') && str_contains($loginContent, 'kasir@toko.com'), "Login page contains demo account helpers");

// 4. Test Kasir Access Restrictions
$kasir = new App\Models\User(['id' => 999, 'name' => 'Dewi Kasir', 'email' => 'dewi@toko.com', 'role' => 'kasir']);

// Kasir attempting to access Admin-only /barang
$respKasirBarang = simulateRequest('GET', '/barang', $kasir);
assertCondition($respKasirBarang->isRedirection() && str_contains($respKasirBarang->headers->get('Location'), 'kasir'), 
    "Kasir attempting /barang is blocked and redirected to /kasir (Status: {$respKasirBarang->getStatusCode()})");

// Kasir attempting to access Admin-only /transaksi
$respKasirTrans = simulateRequest('GET', '/transaksi', $kasir);
assertCondition($respKasirTrans->isRedirection() && str_contains($respKasirTrans->headers->get('Location'), 'kasir'), 
    "Kasir attempting /transaksi is blocked and redirected to /kasir (Status: {$respKasirTrans->getStatusCode()})");

// Kasir attempting to access Admin-only /pengaturan
$respKasirSettings = simulateRequest('GET', '/pengaturan', $kasir);
assertCondition($respKasirSettings->isRedirection() && str_contains($respKasirSettings->headers->get('Location'), 'kasir'), 
    "Kasir attempting /pengaturan is blocked and redirected to /kasir (Status: {$respKasirSettings->getStatusCode()})");

// Kasir attempting to access Admin-only /pengguna (Manajemen Akun)
$respKasirUsers = simulateRequest('GET', '/pengguna', $kasir);
assertCondition($respKasirUsers->isRedirection() && str_contains($respKasirUsers->headers->get('Location'), 'kasir'), 
    "Kasir attempting /pengguna is blocked and redirected to /kasir (Status: {$respKasirUsers->getStatusCode()})");

// 5. Test Kasir Allowed Pages
// Note: rendering database views might need database, but middleware authorization check runs first.
// Let's verify Route resolution and middleware stack:
$router = $app->make('router');

$kasirRoute = $router->getRoutes()->getByName('kasir.index');
$kasirMiddleware = $kasirRoute->gatherMiddleware();
assertCondition(in_array('auth', $kasirMiddleware) && !in_array('role:admin', $kasirMiddleware), 
    "Route kasir.index requires 'auth' and DOES NOT restrict to admin");

$stokRoute = $router->getRoutes()->getByName('stok.index');
$stokMiddleware = $stokRoute->gatherMiddleware();
assertCondition(in_array('auth', $stokMiddleware) && !in_array('role:admin', $stokMiddleware), 
    "Route stok.index requires 'auth' and DOES NOT restrict to admin");

$dashboardRoute = $router->getRoutes()->getByName('dashboard');
$dashboardMiddleware = $dashboardRoute->gatherMiddleware();
assertCondition(in_array('auth', $dashboardMiddleware) && !in_array('role:admin', $dashboardMiddleware), 
    "Route dashboard requires 'auth' and DOES NOT restrict to admin");

$barangRoute = $router->getRoutes()->getByName('products.index');
$barangMiddleware = $barangRoute->gatherMiddleware();
assertCondition(in_array('role:admin', $barangMiddleware), 
    "Route products.index is properly protected with 'role:admin'");

$transaksiRoute = $router->getRoutes()->getByName('transaksi.index');
$transaksiMiddleware = $transaksiRoute->gatherMiddleware();
assertCondition(in_array('role:admin', $transaksiMiddleware), 
    "Route transaksi.index is properly protected with 'role:admin'");

$laporanRoute = $router->getRoutes()->getByName('laporan.index');
$laporanMiddleware = $laporanRoute->gatherMiddleware();
assertCondition(in_array('role:admin', $laporanMiddleware), 
    "Route laporan.index is properly protected with 'role:admin'");

$pengaturanRoute = $router->getRoutes()->getByName('pengaturan.index');
$pengaturanMiddleware = $pengaturanRoute->gatherMiddleware();
assertCondition(in_array('role:admin', $pengaturanMiddleware), 
    "Route pengaturan.index is properly protected with 'role:admin'");

$penggunaRoute = $router->getRoutes()->getByName('pengguna.index');
$penggunaMiddleware = $penggunaRoute->gatherMiddleware();
assertCondition(in_array('role:admin', $penggunaMiddleware), 
    "Route pengguna.index is properly protected with 'role:admin'");

echo "\n========================================\n";
echo " RESULT: $testsPassed PASSED, $testsFailed FAILED\n";
echo "========================================\n";

if ($testsFailed > 0) {
    exit(1);
}
