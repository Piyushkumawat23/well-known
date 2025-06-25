<?php

// Show PHP errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan; // ✅ FIXED: Import Artisan class

$kernel = $app->make(Kernel::class);

// Run Laravel
$kernel->bootstrap();

echo "<pre>";

Artisan::call('config:clear');
echo Artisan::output();

Artisan::call('cache:clear');
echo Artisan::output();

Artisan::call('route:clear');
echo Artisan::output();

Artisan::call('view:clear');
echo Artisan::output();

echo "✅ Artisan commands executed.";
echo "</pre>";
