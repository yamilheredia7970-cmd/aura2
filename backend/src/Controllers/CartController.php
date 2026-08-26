<?php

declare(strict_types=1);

namespace Backend\Controllers;

use Backend\Middleware\AuthMiddleware;
use Backend\Models\Cart;
use Backend\Models\Product;
use Backend\Support\Response;
use Backend\Support\ValidationException;
use Backend\Support\Validator;

final class CartController
{
    public static function show(): void
    {
        $cartId = Cart::resolveId(AuthMiddleware::currentUserId());
        Response::json(['items' => Cart::items($cartId)]);
    }

    public static function addItem(): void
    {
        try {
            $data = Validator::validate(self::body(), [
                'product_id' => 'required|int',
                'variant_id' => 'required|int',
                'quantity' => 'required|int',
            ]);
        } catch (ValidationException $e) {
            Response::error('Datos inválidos.', 422, $e->errors());
        }

        $quantity = (int) $data['quantity'];
        if ($quantity < 1) {
            Response::error('La cantidad debe ser al menos 1.', 422);
        }

        $variant = Product::findVariant((int) $data['product_id'], (int) $data['variant_id']);
        if (!$variant) {
            Response::error('La combinación de talla/color no existe para este producto.', 404);
        }

        $cartId = Cart::resolveId(AuthMiddleware::currentUserId());
        Cart::addItem($cartId, (int) $data['product_id'], (int) $data['variant_id'], $quantity);

        Response::json(['items' => Cart::items($cartId)], 201);
    }

    public static function updateItem(array $params): void
    {
        try {
            $data = Validator::validate(self::body(), ['quantity' => 'required|int']);
        } catch (ValidationException $e) {
            Response::error('Datos inválidos.', 422, $e->errors());
        }

        $quantity = (int) $data['quantity'];
        if ($quantity < 1) {
            Response::error('La cantidad debe ser al menos 1.', 422);
        }

        $cartId = Cart::resolveId(AuthMiddleware::currentUserId());
        $updated = Cart::updateItemQuantity($cartId, (int) $params['id'], $quantity);

        if (!$updated) {
            Response::error('Ítem no encontrado en el carrito.', 404);
        }

        Response::json(['items' => Cart::items($cartId)]);
    }

    public static function removeItem(array $params): void
    {
        $cartId = Cart::resolveId(AuthMiddleware::currentUserId());
        $removed = Cart::removeItem($cartId, (int) $params['id']);

        if (!$removed) {
            Response::error('Ítem no encontrado en el carrito.', 404);
        }

        Response::json(['items' => Cart::items($cartId)]);
    }

    private static function body(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
