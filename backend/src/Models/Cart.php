<?php

declare(strict_types=1);

namespace Backend\Models;

use Backend\Database\Connection;
use PDO;

final class Cart
{
    public const GUEST_COOKIE = 'guest_cart_token';

    public static function resolveId(?int $userId): int
    {
        if ($userId !== null) {
            return self::getOrCreateForUser($userId);
        }

        return self::getOrCreateForGuest();
    }

    private static function getOrCreateForUser(int $userId): int
    {
        $pdo = Connection::get();
        $stmt = $pdo->prepare('SELECT id FROM carts WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $cart = $stmt->fetch();

        if ($cart) {
            return (int) $cart['id'];
        }

        $stmt = $pdo->prepare('INSERT INTO carts (user_id) VALUES (:user_id)');
        $stmt->execute(['user_id' => $userId]);

        return (int) $pdo->lastInsertId();
    }

    private static function getOrCreateForGuest(): int
    {
        $pdo = Connection::get();
        $token = $_COOKIE[self::GUEST_COOKIE] ?? null;

        if ($token) {
            $stmt = $pdo->prepare('SELECT id FROM carts WHERE guest_token = :token');
            $stmt->execute(['token' => $token]);
            $cart = $stmt->fetch();
            if ($cart) {
                return (int) $cart['id'];
            }
        }

        $token = bin2hex(random_bytes(32));
        setcookie(self::GUEST_COOKIE, $token, [
            'expires' => time() + (60 * 60 * 24 * 30),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        $stmt = $pdo->prepare('INSERT INTO carts (guest_token) VALUES (:token)');
        $stmt->execute(['token' => $token]);

        return (int) $pdo->lastInsertId();
    }

    public static function mergeGuestIntoUser(int $userId): void
    {
        $token = $_COOKIE[self::GUEST_COOKIE] ?? null;
        if (!$token) {
            return;
        }

        $pdo = Connection::get();
        $stmt = $pdo->prepare('SELECT id FROM carts WHERE guest_token = :token');
        $stmt->execute(['token' => $token]);
        $guestCart = $stmt->fetch();

        if (!$guestCart) {
            return;
        }

        $userCartId = self::getOrCreateForUser($userId);
        $guestCartId = (int) $guestCart['id'];

        if ($userCartId === $guestCartId) {
            return;
        }

        $items = $pdo->prepare('SELECT variant_id, product_id, quantity FROM cart_items WHERE cart_id = :cart_id');
        $items->execute(['cart_id' => $guestCartId]);

        foreach ($items->fetchAll() as $item) {
            self::addItem($userCartId, (int) $item['product_id'], (int) $item['variant_id'], (int) $item['quantity']);
        }

        $pdo->prepare('DELETE FROM carts WHERE id = :id')->execute(['id' => $guestCartId]);
        setcookie(self::GUEST_COOKIE, '', ['expires' => time() - 3600, 'path' => '/']);
    }

    public static function items(int $cartId): array
    {
        $stmt = Connection::get()->prepare(
            'SELECT ci.id, ci.quantity, ci.product_id, ci.variant_id,
                    p.name, p.price, p.image_url,
                    v.size, v.color, v.stock
             FROM cart_items ci
             JOIN products p ON p.id = ci.product_id
             JOIN product_variants v ON v.id = ci.variant_id
             WHERE ci.cart_id = :cart_id'
        );
        $stmt->execute(['cart_id' => $cartId]);

        return $stmt->fetchAll();
    }

    public static function addItem(int $cartId, int $productId, int $variantId, int $quantity): void
    {
        $pdo = Connection::get();
        $stmt = $pdo->prepare(
            'SELECT id, quantity FROM cart_items WHERE cart_id = :cart_id AND variant_id = :variant_id'
        );
        $stmt->execute(['cart_id' => $cartId, 'variant_id' => $variantId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $update = $pdo->prepare('UPDATE cart_items SET quantity = :quantity WHERE id = :id');
            $update->execute(['quantity' => $existing['quantity'] + $quantity, 'id' => $existing['id']]);
            return;
        }

        $insert = $pdo->prepare(
            'INSERT INTO cart_items (cart_id, product_id, variant_id, quantity)
             VALUES (:cart_id, :product_id, :variant_id, :quantity)'
        );
        $insert->execute([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $quantity,
        ]);
    }

    public static function updateItemQuantity(int $cartId, int $itemId, int $quantity): bool
    {
        $stmt = Connection::get()->prepare(
            'UPDATE cart_items SET quantity = :quantity WHERE id = :id AND cart_id = :cart_id'
        );
        $stmt->execute(['quantity' => $quantity, 'id' => $itemId, 'cart_id' => $cartId]);

        return $stmt->rowCount() > 0;
    }

    public static function removeItem(int $cartId, int $itemId): bool
    {
        $stmt = Connection::get()->prepare('DELETE FROM cart_items WHERE id = :id AND cart_id = :cart_id');
        $stmt->execute(['id' => $itemId, 'cart_id' => $cartId]);

        return $stmt->rowCount() > 0;
    }

    public static function clear(int $cartId): void
    {
        $stmt = Connection::get()->prepare('DELETE FROM cart_items WHERE cart_id = :cart_id');
        $stmt->execute(['cart_id' => $cartId]);
    }
}
