<?php

declare(strict_types=1);

namespace Backend\Middleware;

use Backend\Support\Csrf;
use Backend\Support\Response;

final class CsrfMiddleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public static function verify(): void
    {
        if (in_array($_SERVER['REQUEST_METHOD'], self::SAFE_METHODS, true)) {
            return;
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Csrf::verify($token)) {
            Response::error('Token CSRF inválido o ausente.', 419);
        }
    }
}
