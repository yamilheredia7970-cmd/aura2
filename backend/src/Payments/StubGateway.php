<?php

declare(strict_types=1);

namespace Backend\Payments;

use Backend\Database\Connection;

/**
 * Placeholder gateway while no real integration is connected.
 * Replace with StripeGateway / MercadoPagoGateway implementing the same interface.
 */
final class StubGateway implements PaymentGatewayInterface
{
    public function createPayment(array $order): array
    {
        $stmt = Connection::get()->prepare(
            'INSERT INTO payments (order_id, gateway, status, external_reference, amount)
             VALUES (:order_id, :gateway, :status, :reference, :amount)'
        );
        $reference = 'stub_' . bin2hex(random_bytes(8));
        $stmt->execute([
            'order_id' => $order['id'],
            'gateway' => 'stub',
            'status' => 'pending',
            'reference' => $reference,
            'amount' => $order['total'],
        ]);

        return [
            'gateway' => 'stub',
            'status' => 'pending',
            'reference' => $reference,
            'message' => 'Simulated payment: replace StubGateway with a real integration (Stripe/MercadoPago).',
        ];
    }
}
