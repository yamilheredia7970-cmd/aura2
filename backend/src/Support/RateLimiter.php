<?php

declare(strict_types=1);

namespace Backend\Support;

use Backend\Database\Connection;

final class RateLimiter
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_MINUTES = 15;

    public static function tooManyAttempts(string $email, string $ip): bool
    {
        $pdo = Connection::get();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE email = :email AND ip_address = :ip
               AND attempted_at > (NOW() - INTERVAL :minutes MINUTE)'
        );
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':minutes', self::WINDOW_MINUTES, \PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn() >= self::MAX_ATTEMPTS;
    }

    public static function recordFailedAttempt(string $email, string $ip): void
    {
        $pdo = Connection::get();
        $stmt = $pdo->prepare('INSERT INTO login_attempts (email, ip_address) VALUES (:email, :ip)');
        $stmt->execute(['email' => $email, 'ip' => $ip]);
    }

    public static function clearAttempts(string $email, string $ip): void
    {
        $pdo = Connection::get();
        $stmt = $pdo->prepare('DELETE FROM login_attempts WHERE email = :email AND ip_address = :ip');
        $stmt->execute(['email' => $email, 'ip' => $ip]);
    }
}
