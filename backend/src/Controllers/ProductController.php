<?php

declare(strict_types=1);

namespace Backend\Controllers;

use Backend\Models\Product;
use Backend\Support\Response;

final class ProductController
{
    public static function index(): void
    {
        $filters = [
            'category' => $_GET['category'] ?? null,
            'size' => $_GET['size'] ?? null,
            'color' => $_GET['color'] ?? null,
            'price_max' => $_GET['price_max'] ?? null,
        ];

        Response::json(['products' => Product::search($filters)]);
    }

    public static function show(array $params): void
    {
        $product = Product::find((int) $params['id']);

        if (!$product) {
            Response::error('Producto no encontrado.', 404);
        }

        Response::json(['product' => $product]);
    }
}
