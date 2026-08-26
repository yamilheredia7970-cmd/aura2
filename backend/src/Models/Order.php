<?php

declare(strict_types=1);

namespace Backend\Models;

use Backend\Database\Connection;
use RuntimeException;

final class Order
{
    /**
     * Crea una orden a partir del carrito, valida stock, descuenta inventario
     * y vacía el carrito. Todo dentro de una transacción.
     *
     * @throws RuntimeException si algún ítem no tiene stock suficiente
     */
    public static function createFromCart(int $cartId, int $userId, string $shippingAddress): array
    {
        $pdo = Connection::get();
        $items = Cart::items($cartId);

        if ($items === []) {
            throw new RuntimeException('El carrito está vacío.');
        }

        foreach ($items as $item) {
            if ($item['quantity'] > $item['stock']) {
                throw new RuntimeException("Stock insuficiente para {$item['name']} ({$item['size']}/{$item['color']}).");
            }
        }

        $total = array_sum(array_map(
            fn (array $item) => $item['price'] * $item['quantity'],
            $items
        ));

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO orders (user_id, total, shipping_address) VALUES (:user_id, :total, :shipping_address)'
            );
            $stmt->execute(['user_id' => $userId, 'total' => $total, 'shipping_address' => $shippingAddress]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items (order_id, product_id, variant_id, product_name, size, color, quantity, unit_price)
                 VALUES (:order_id, :product_id, :variant_id, :product_name, :size, :color, :quantity, :unit_price)'
            );

            foreach ($items as $item) {
                $itemStmt->execute([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'product_name' => $item['name'],
                    'size' => $item['size'],
                    'color' => $item['color'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                ]);

                Product::decrementStock((int) $item['variant_id'], (int) $item['quantity']);
            }

            Cart::clear($cartId);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return self::find($orderId, $userId);
    }

    public static function listForUser(int $userId): array
    {
        $stmt = Connection::get()->prepare(
            'SELECT id, status, total, shipping_address, created_at FROM orders
             WHERE user_id = :user_id ORDER BY created_at DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public static function find(int $orderId, int $userId): ?array
    {
        $stmt = Connection::get()->prepare(
            'SELECT * FROM orders WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute(['id' => $orderId, 'user_id' => $userId]);
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        $itemsStmt = Connection::get()->prepare('SELECT * FROM order_items WHERE order_id = :order_id');
        $itemsStmt->execute(['order_id' => $orderId]);
        $order['items'] = $itemsStmt->fetchAll();

        return $order;
    }

    public static function updateStatus(int $orderId, string $status): void
    {
        $stmt = Connection::get()->prepare('UPDATE orders SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $orderId]);
    }
}
