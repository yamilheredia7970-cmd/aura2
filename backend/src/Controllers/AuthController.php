<?php

declare(strict_types=1);

namespace Backend\Controllers;

use Backend\Middleware\AuthMiddleware;
use Backend\Models\Cart;
use Backend\Models\User;
use Backend\Support\RateLimiter;
use Backend\Support\Response;
use Backend\Support\ValidationException;
use Backend\Support\Validator;

final class AuthController
{
    public static function register(): void
    {
        try {
            $data = Validator::validate(self::body(), [
                'name' => 'required|max:120',
                'email' => 'required|email|max:180',
                'password' => 'required|min:8',
            ]);
        } catch (ValidationException $e) {
            Response::error('Datos inválidos.', 422, $e->errors());
        }

        if (User::findByEmail($data['email'])) {
            Response::error('Ese email ya está registrado.', 409);
        }

        $userId = User::create($data['name'], $data['email'], $data['password']);
        self::establishSession($userId);

        Response::json(['user' => User::findById($userId)], 201);
    }

    public static function login(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        try {
            $data = Validator::validate(self::body(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);
        } catch (ValidationException $e) {
            Response::error('Datos inválidos.', 422, $e->errors());
        }

        if (RateLimiter::tooManyAttempts($data['email'], $ip)) {
            Response::error('Demasiados intentos. Probá de nuevo en unos minutos.', 429);
        }

        $user = User::findByEmail($data['email']);

        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            RateLimiter::recordFailedAttempt($data['email'], $ip);
            Response::error('Credenciales inválidas.', 401);
        }

        RateLimiter::clearAttempts($data['email'], $ip);
        self::establishSession((int) $user['id']);

        Response::json(['user' => User::findById((int) $user['id'])]);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        Response::json(['message' => 'Sesión cerrada.']);
    }

    public static function me(): void
    {
        $userId = AuthMiddleware::currentUserId();
        if (!$userId) {
            Response::error('No autenticado.', 401);
        }

        Response::json(['user' => User::findById($userId)]);
    }

    private static function establishSession(int $userId): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        Cart::mergeGuestIntoUser($userId);
    }

    private static function body(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
