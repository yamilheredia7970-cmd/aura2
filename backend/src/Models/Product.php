<?php

declare(strict_types=1);

namespace Backend\Models;

use Backend\Database\Connection;

final class Product
{
    /**
     * @param array{category?: string, size?: string, color?: string, price_max?: string} $filters
     */
    public static function search(array $filters): array
    {
        $sql = 'SELECT DISTINCT p.* FROM products p';
        $conditions = [];
        $params = [];

        $needsVariantJoin = !empty($filters['size']) || !empty($filters['color']);
        if ($needsVariantJoin) {
            $sql .= ' JOIN product_variants v ON v.product_id = p.id';
        }

        if (!empty($filters['category'])) {
            $sql .= ' JOIN categories c ON c.id = p.category_id';
            $conditions[] = 'c.name = :category';
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['size'])) {
            $conditions[] = 'v.size = :size';
            $params['size'] = $filters['size'];
        }

        if (!empty($filters['color'])) {
            $conditions[] = 'v.color = :color';
            $params['color'] = $filters['color'];
        }

        if (!empty($filters['price_max']) && is_numeric($filters['price_max'])) {
            $conditions[] = 'p.price <= :price_max';
            $params['price_max'] = $filters['price_max'];
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY p.created_at DESC';

        $stmt = Connection::get()->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        return array_map(fn (array $product) => self::attachVariants($product), $products);
    }

    public static function find(int $id): ?array
    {
        $stmt = Connection::get()->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch();

        return $product ? self::attachVariants($product) : null;
    }

    public static function findVariant(int $productId, int $variantId): ?array
    {
        $stmt = Connection::get()->prepare(
            'SELECT * FROM product_variants WHERE id = :variant_id AND product_id = :product_id'
        );
        $stmt->execute(['variant_id' => $variantId, 'product_id' => $productId]);
        $variant = $stmt->fetch();

        return $variant ?: null;
    }

    public static function decrementStock(int $variantId, int $quantity): void
    {
        $stmt = Connection::get()->prepare(
            'UPDATE product_variants SET stock = stock - :quantity WHERE id = :id AND stock >= :quantity'
        );
        $stmt->execute(['quantity' => $quantity, 'id' => $variantId]);
    }

    private static function attachVariants(array $product): array
    {
        $stmt = Connection::get()->prepare(
            'SELECT id, size, color, stock FROM product_variants WHERE product_id = :product_id'
        );
        $stmt->execute(['product_id' => $product['id']]);
        $product['variants'] = $stmt->fetchAll();

        return $product;
    }
}
