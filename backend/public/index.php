<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Backend\Controllers\AuthController;
use Backend\Controllers\CartController;
use Backend\Controllers\CategoryController;
use Backend\Controllers\OrderController;
use Backend\Controllers\ProductController;
use Backend\Middleware\AuthMiddleware;
use Backend\Middleware\CorsMiddleware;
use Backend\Middleware\CsrfMiddleware;
use Backend\Router;
use Backend\Support\Csrf;
use Backend\Support\Env;
use Backend\Support\Response;

Env::load(__DIR__ . '/../.env');

CorsMiddleware::handle();

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => (Env::get('APP_ENV') === 'production'),
]);
session_start();

$router = new Router();

$router->get('/api/csrf-token', fn () => Response::json(['csrf_token' => Csrf::token()]));

$router->post('/api/auth/register', [AuthController::class, 'register'], [CsrfMiddleware::verify(...)]);
$router->post('/api/auth/login', [AuthController::class, 'login'], [CsrfMiddleware::verify(...)]);
$router->post('/api/auth/logout', [AuthController::class, 'logout'], [CsrfMiddleware::verify(...)]);
$router->get('/api/auth/me', [AuthController::class, 'me']);

$router->get('/api/categories', [CategoryController::class, 'index']);

$router->get('/api/products', [ProductController::class, 'index']);
$router->get('/api/products/{id}', [ProductController::class, 'show']);

$router->get('/api/cart', [CartController::class, 'show']);
$router->post('/api/cart/items', [CartController::class, 'addItem'], [CsrfMiddleware::verify(...)]);
$router->patch('/api/cart/items/{id}', [CartController::class, 'updateItem'], [CsrfMiddleware::verify(...)]);
$router->delete('/api/cart/items/{id}', [CartController::class, 'removeItem'], [CsrfMiddleware::verify(...)]);

$router->post('/api/orders', [OrderController::class, 'store'], [
    CsrfMiddleware::verify(...),
    AuthMiddleware::requireAuth(...),
]);
$router->get('/api/orders', [OrderController::class, 'index'], [AuthMiddleware::requireAuth(...)]);
$router->get('/api/orders/{id}', [OrderController::class, 'show'], [AuthMiddleware::requireAuth(...)]);
$router->post('/api/orders/{id}/pay', [OrderController::class, 'pay'], [
    CsrfMiddleware::verify(...),
    AuthMiddleware::requireAuth(...),
]);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
