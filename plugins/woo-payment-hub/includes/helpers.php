<?php
if (!defined('ABSPATH')) exit;

/**
 * Génère un token sécurisé pour le Payment Hub
 */
function payment_hub_generate_token(WC_Order $order, string $secret): string {

    $data = [
        'order_id' => $order->get_id(),
        'amount'   => $order->get_total(),
        'time'     => time()
    ];

    return Payment_Hub_HMAC::sign($data, $secret);
}

/**
 * Vérifie une signature entrante depuis le Payment Hub
 */
function payment_hub_verify_signature(array $payload, string $secret): bool {

    if (!isset($payload['signature'])) {
        return false;
    }

    $signature = $payload['signature'];
    unset($payload['signature']);

    return Payment_Hub_HMAC::verify($payload, $secret, $signature);
}
