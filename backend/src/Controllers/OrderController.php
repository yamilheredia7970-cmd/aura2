<?php

declare(strict_types=1);

namespace Backend\Controllers;

use Backend\Middleware\AuthMiddleware;
use Backend\Models\Cart;
use Backend\Models\Order;
use Backend\Payments\StubGateway;
use Backend\Support\Response;
use Backend\Support\ValidationException;
use Backend\Support\Validator;
use RuntimeException;

final class OrderController
{
    public static function store(): void
    {
        $userId = AuthMiddleware::currentUserId();

        try {
            $data = Validator::validate(self::body(), [
                'shipping_address' => 'required|max:500',
            ]);
        } catch (ValidationException $e) {
            Response::error('Invalid data.', 422, $e->errors());
        }

        $cartId = Cart::resolveId($userId);

        try {
            $order = Order::createFromCart($cartId, $userId, $data['shipping_address']);
        } catch (RuntimeException $e) {
            Response::error($e->getMessage(), 422);
        }

        Response::json(['order' => $order], 201);
    }

    public static function index(): void
    {
        Response::json(['orders' => Order::listForUser(AuthMiddleware::currentUserId())]);
    }

    public static function show(array $params): void
    {
        $order = Order::find((int) $params['id'], AuthMiddleware::currentUserId());

        if (!$order) {
            Response::error('Order not found.', 404);
        }

        Response::json(['order' => $order]);
    }

    public static function pay(array $params): void
    {
        $order = Order::find((int) $params['id'], AuthMiddleware::currentUserId());

        if (!$order) {
            Response::error('Order not found.', 404);
        }

        $gateway = new StubGateway();
        $result = $gateway->createPayment($order);

        Response::json(['payment' => $result]);
    }

    private static function body(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
