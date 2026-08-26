<?php

declare(strict_types=1);

namespace Backend\Middleware;

use Backend\Support\Env;

final class CorsMiddleware
{
    public static function handle(): void
    {
        $allowedOrigin = Env::get('ALLOWED_ORIGIN', 'http://localhost:3000');
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if ($origin === $allowedOrigin) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Access-Control-Allow-Credentials: true');
            header('Vary: Origin');
        }

        header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
