<?php

declare(strict_types=1);

namespace Backend\Middleware;

use Backend\Support\Response;

final class AuthMiddleware
{
    public static function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            Response::error('No autenticado.', 401);
        }
    }

    public static function currentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }
}
