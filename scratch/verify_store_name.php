<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Setup in-memory sqlite
$config = $app->make('config');
$config->set('database.default', 'sqlite');
$config->set('database.connections.sqlite', [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
]);

// Run minimal migrations
Illuminate\Support\Facades\Artisan::call('migrate', [
    '--path' => [
        'database/migrations/2014_10_12_000000_create_users_table.php',
        'database/migrations/2026_11_04_000001_add_role_to_users_table.php',
        'database/migrations/2024_01_01_000001_create_products_table.php',
        'database/migrations/2026_11_04_000001_create_settings_table.php',
    ],
]);

// Update store_name setting in database
App\Models\Setting::set('store_name', 'Toko Ana Barokah');

// Simulate authenticated user
$user = new App\Models\User(['name' => 'Ana Owner', 'email' => 'ana@toko.com', 'role' => 'admin']);
Illuminate\Support\Facades\Auth::setUser($user);

// Render layout (appSettings automatically loaded via View::composer from Setting model)
$rendered = view('layouts.app', [
    'errors' => new \Illuminate\Support\ViewErrorBag(),
])->render();

$hasStoreNameInHeader = str_contains($rendered, 'Toko Ana Barokah');
$hasCustomTitle = str_contains($rendered, '<title>Toko Ana Barokah - Inventaris & Kasir</title>');

echo "Dynamic Store Name in Sidebar Header: " . ($hasStoreNameInHeader ? "PASS" : "FAIL") . "\n";
echo "Dynamic Store Name in <title>: " . ($hasCustomTitle ? "PASS" : "FAIL") . "\n";

if ($hasStoreNameInHeader && $hasCustomTitle) {
    echo "VERIFICATION SUCCESSFUL!\n";
    exit(0);
} else {
    echo "VERIFICATION FAILED!\n";
    exit(1);
}
