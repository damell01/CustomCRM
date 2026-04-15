<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Hostinger / Flat-Structure Mode
|--------------------------------------------------------------------------
| On shared hosting (Hostinger) the document root IS public_html/ and we
| cannot point Apache to a /public subfolder. When APP_FLAT_PUBLIC=true
| this file lives at the project root alongside vendor/, app/, etc.
| Paths are adjusted accordingly (no /../ jumps needed).
|--------------------------------------------------------------------------
*/
$flat = getenv('APP_FLAT_PUBLIC') === 'true';

// Determine if the application is in maintenance mode...
$maintenancePath = $flat
    ? __DIR__.'/storage/framework/maintenance.php'
    : __DIR__.'/../storage/framework/maintenance.php';

if (file_exists($maintenancePath)) {
    require $maintenancePath;
}

// Register the Composer autoloader...
$flat
    ? require __DIR__.'/vendor/autoload.php'
    : require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
$bootstrapPath = $flat
    ? __DIR__.'/bootstrap/app.php'
    : __DIR__.'/../bootstrap/app.php';

(require_once $bootstrapPath)->handleRequest(Request::capture());
