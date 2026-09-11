<?php

/**
 * Vercel PHP serverless entry point for Laravel.
 * Placed at planner/api/index.php — one level above public/.
 */

define('LARAVEL_START', microtime(true));

$laravelPublic = __DIR__ . '/../public';

// Change working directory so Laravel's relative paths resolve correctly.
chdir($laravelPublic);

$_SERVER['DOCUMENT_ROOT']   = $laravelPublic;
$_SERVER['SCRIPT_FILENAME'] = $laravelPublic . '/index.php';
$_SERVER['SCRIPT_NAME']     = '/index.php';

// Maintenance mode
if (file_exists($maintenance = $laravelPublic . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoloader
require $laravelPublic . '/../vendor/autoload.php';

// Bootstrap & handle
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

/** @var Application $app */
$app = require_once $laravelPublic . '/../bootstrap/app.php';
$app->handleRequest(Request::capture());
