<?php

add_action('rest_api_init', function () {

    register_rest_route('payment/v1', '/confirm', [
        'methods' => 'POST',
        'callback' => 'payment_hub_confirm',
        'permission_callback' => '__return_true'
    ]);
});

function payment_hub_confirm(WP_REST_Request $request)
{
    $data = $request->get_json_params();
    $secret = PAYMENT_HUB_SECRET;

    $signature = $data['signature'];
    unset($data['signature']);

    ksort($data);
    $expected = hash_hmac('sha256', json_encode($data), $secret);

    if (!hash_equals($expected, $signature)) {
        return new WP_REST_Response(['error' => 'Invalid signature'], 403);
    }

    $order = wc_get_order($data['order_id']);

    if (!$order) {
        return new WP_REST_Response(['error' => 'Order not found'], 404);
    }

    /**
     * 🔥 GESTION PROPRE DU STATUT
     */

    if ($data['status'] === 'validated') {

        if ($order->has_status(['completed', 'processing'])) {
            return new WP_REST_Response(['success' => true], 200);
        }

        $order->payment_complete();
        $order->add_order_note('Paiement validé via Payment Hub');

    } elseif ($data['status'] === 'rejected') {

        /**
         * ⚠️ REJET PROPRE
         * - on ne supprime PAS la commande
         * - on ne bloque PAS définitivement le client
         * - on laisse une nouvelle tentative possible
         */

        if (!$order->has_status(['failed', 'cancelled'])) {
            $order->update_status(
                'failed',
                'Paiement rejeté par le Payment Hub'
            );
        }

        // Option UX : message visible client
        wc_add_notice(
            'Votre paiement a été rejeté. Veuillez réessayer.',
            'error'
        );
    }

    return new WP_REST_Response(['success' => true], 200);
}
