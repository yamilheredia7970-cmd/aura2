<?php

declare(strict_types=1);

namespace Backend\Models;

use Backend\Database\Connection;

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Connection::get()->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Connection::get()->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public static function create(string $name, string $email, string $password): int
    {
        $stmt = Connection::get()->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)'
        );
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return (int) Connection::get()->lastInsertId();
    }
}
