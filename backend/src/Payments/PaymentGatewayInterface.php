<?php

declare(strict_types=1);

namespace Backend\Payments;

interface PaymentGatewayInterface
{
    /**
     * Starts a payment for the given order and returns the data the frontend
     * needs to continue (redirect URL, client secret, etc.).
     *
     * @param array $order the order row (see Models\Order::find)
     */
    public function createPayment(array $order): array;
}
