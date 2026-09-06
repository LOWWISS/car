<?php
/**
 * Front controller / entry point.
 *
 * All requests route through here (via .htaccess). It:
 *  1. Loads config (.env)
 *  2. Registers the autoloader for app/ classes (PSR-4-ish, no composer needed)
 *  3. Starts the session + sends security headers
 *  4. Registers the error handler
 *  5. Builds the router and dispatches the request
 */

declare(strict_types=1);

// Set timezone to match MySQL server (avoids auction start/end mismatches).
// Adjust to your server's timezone. Asia/Manila = UTC+8 (WAMP default is often
// the system timezone; ensure this matches your MySQL timezone).
date_default_timezone_set('Asia/Manila');

// ----- Paths -----
define('APP_ROOT', dirname(__DIR__));
define('APP_DIR',  APP_ROOT . '/app');

// ----- Autoloader (no composer required) -----
spl_autoload_register(function (string $class): void {
    // Core, Controllers, Models live in app/<Folder>/<Class>.php
    $file = APP_DIR . '/Core/' . $class . '.php';
    if (is_file($file)) { require $file; return; }
    $file = APP_DIR . '/Controllers/' . $class . '.php';
    if (is_file($file)) { require $file; return; }
    $file = APP_DIR . '/Models/' . $class . '.php';
    if (is_file($file)) { require $file; return; }
});

// ----- Config -----
require APP_ROOT . '/config/config.php';
Config::load(APP_ROOT . '/.env');

// ----- Global helpers (e(), config(), asset()) — loaded early so they are
//       available in controllers/models/views regardless of autoloader timing.
require APP_DIR . '/Core/helpers.php';

// ----- Error handling (after config so APP_DEBUG is available) -----
ErrorHandler::register();

// ----- Security headers (sent on every response) -----
SecurityHeaders::send();

// ----- Session -----
Session::start();

// ----- Serve static files (PHP built-in server only; Apache handles this via .htaccess) -----
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $staticFile = __DIR__ . $path;
    if ($path !== '/' && is_file($staticFile)) {
        return false; // Let the built-in server serve the file directly
    }
}

// ----- Router -----
$router = new Router();

// Home
$router->add('GET',  '/',               'HomeController@index');

// Auth
$router->add('GET',  '/login',          'AuthController@showLogin');
$router->add('POST', '/login',          'AuthController@login');
$router->add('GET',  '/register',       'AuthController@showRegister');
$router->add('POST', '/register',       'AuthController@register');
$router->add('GET',  '/verify-email/{token}', 'AuthController@verifyEmail');
$router->add('POST', '/logout',         'AuthController@logout');
$router->add('GET',  '/forgot-password','AuthController@showForgot');
$router->add('POST', '/forgot-password','AuthController@sendReset');
$router->add('GET',  '/reset-password/{token}', 'AuthController@showReset');
$router->add('POST', '/reset-password', 'AuthController@resetPassword');

// Cars (catalog + detail + CRUD)
$router->add('GET',  '/cars',           'CarController@catalog');
$router->add('GET',  '/cars/bidding',   'CarController@bidding');
$router->add('GET',  '/cars/featured',  'CarController@featured');
$router->add('GET',  '/cars/view/{id}', 'CarController@show');
$router->add('GET',  '/cars/create',    'CarController@createForm');
$router->add('POST', '/cars/create',    'CarController@create');
$router->add('GET',  '/cars/edit/{id}', 'CarController@editForm');
$router->add('POST', '/cars/edit/{id}', 'CarController@update');
$router->add('POST', '/cars/delete/{id}','CarController@delete');
$router->add('POST', '/cars/delete-image/{imageId}', 'CarController@deleteImage');

// Bids
$router->add('POST', '/bids/place/{carId}',     'BidController@place');
$router->add('POST', '/bids/ajax/{carId}',      'BidController@placeAjax');
$router->add('POST', '/bids/buy-now/{carId}',   'BidController@buyNow');
$router->add('GET',  '/bids/history/{carId}',   'BidController@history');

// Watchlist
$router->add('POST', '/watchlist/toggle',       'WatchlistController@toggle');
$router->add('GET',  '/watchlist',              'WatchlistController@list');

// Notifications
$router->add('GET',  '/notifications',          'NotificationController@index');
$router->add('POST', '/notifications/mark-read','NotificationController@markRead');
$router->add('GET',  '/notifications/unread-count','NotificationController@unreadCount');

// Dashboard
$router->add('GET',  '/dashboard',              'DashboardController@index');
$router->add('GET',  '/dashboard/my-bids',       'DashboardController@myBids');
$router->add('GET',  '/dashboard/won-auctions',  'DashboardController@wonAuctions');

// Transactions (post-auction payment / handover / completion lifecycle)
$router->add('GET',  '/transactions',                       'TransactionController@index');
$router->add('GET',  '/transactions/view/{id}',             'TransactionController@show');
$router->add('POST', '/transactions/mark-paid/{id}',        'TransactionController@markPaid');
$router->add('POST', '/transactions/confirm-payment/{id}',  'TransactionController@confirmPayment');
$router->add('POST', '/transactions/ready-handover/{id}',   'TransactionController@readyHandover');
$router->add('POST', '/transactions/complete/{id}',         'TransactionController@complete');
$router->add('POST', '/transactions/cancel/{id}',           'TransactionController@cancel');

// Admin
$router->add('GET',  '/admin',                  'AdminController@index');
$router->add('GET',  '/admin/review/{id}',      'AdminController@review');
$router->add('POST', '/admin/approve/{id}',     'AdminController@approve');
$router->add('POST', '/admin/reject/{id}',      'AdminController@reject');
$router->add('GET',  '/admin/users',            'AdminController@users');
$router->add('POST', '/admin/users/role/{id}',  'AdminController@updateRole');
$router->add('POST', '/admin/users/toggle/{id}','AdminController@toggleUser');
$router->add('GET',  '/admin/logs',             'AdminController@logs');

// ----- Dispatch -----
$request = new Request();
$router->dispatch($request->method(), $request->path());
