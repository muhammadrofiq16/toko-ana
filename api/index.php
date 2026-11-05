<?php

/**
 * Entry point untuk Vercel Serverless Function
 * Menyiapkan folder temporary di /tmp agar Laravel tidak error read-only
 */

$tmpDirs = [
    '/tmp/views',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($tmpDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Teruskan ke file public/index.php bawaan Laravel
require __DIR__ . '/../public/index.php';
