<?php

/**
 * Vercel PHP entry point for Laravel.
 *
 * Vercel runs serverless PHP functions from the /api directory.
 * This file bootstraps the Laravel application located in /planner
 * and hands all requests to it.
 */

define('LARAVEL_START', microtime(true));

// Absolute paths to the Laravel installation
$laravelBase   = __DIR__ . '/../planner';
$laravelPublic = $laravelBase . '/public';

// Change the working directory to public/ so that Laravel's
// relative path resolution (storage_path, etc.) works correctly.
chdir($laravelPublic);

// Make PHP believe the script lives inside public/ so that
// __DIR__ references in Laravel's bootstrap work as expected.
$_SERVER['DOCUMENT_ROOT']   = $laravelPublic;
$_SERVER['SCRIPT_FILENAME'] = $laravelPublic . '/index.php';
$_SERVER['SCRIPT_NAME']     = '/index.php';

// Maintenance mode check (unchanged from Laravel's public/index.php)
if (file_exists($maintenance = $laravelPublic . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composer autoloader
require $laravelPublic . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the incoming request
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

/** @var Application $app */
$app = require_once $laravelPublic . '/../bootstrap/app.php';
$app->handleRequest(Request::capture());
