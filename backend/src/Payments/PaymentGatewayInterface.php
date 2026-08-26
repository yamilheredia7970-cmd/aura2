<?php

declare(strict_types=1);

namespace Backend\Payments;

interface PaymentGatewayInterface
{
    /**
     * Inicia un pago para la orden dada y devuelve los datos que el frontend
     * necesita para continuar (URL de redirección, client secret, etc.).
     *
     * @param array $order fila de la orden (ver Models\Order::find)
     */
    public function createPayment(array $order): array;
}
