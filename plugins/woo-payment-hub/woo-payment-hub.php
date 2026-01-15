<?php
/**
 * Plugin Name: WooCommerce Payment Hub
 * Description: Paiement manuel sécurisé via Payment Hub externe.
 * Version: 1.0.1
 * Author: Payment Hub Team
 */

if (!defined('ABSPATH')) exit;

add_action('plugins_loaded', function () {

    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    require_once plugin_dir_path(__FILE__) . 'includes/class-gateway-payment-hub.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-hmac.php';
    require_once plugin_dir_path(__FILE__) . 'includes/helpers.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-api-listener.php';

    add_filter('woocommerce_payment_gateways', function ($methods) {
        $methods[] = 'WC_Gateway_Payment_Hub';
        return $methods;
    });

});

/**
 * Scripts admin
 */
add_action('admin_enqueue_scripts', function ($hook) {

    if ($hook !== 'woocommerce_page_wc-settings') return;

    wp_enqueue_script(
        'payment-hub-admin',
        plugin_dir_url(__FILE__) . 'assets/admin.js',
        ['jquery'],
        '1.0.1',
        true
    );

    wp_localize_script('payment-hub-admin', 'PaymentHub', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('payment_hub_test')
    ]);
});

/**
 * Test de connexion Payment Hub
 */
add_action('wp_ajax_payment_hub_test_connection', function () {

    check_ajax_referer('payment_hub_test', 'nonce');

    $gateway = new WC_Gateway_Payment_Hub();

    if (empty($gateway->hub_url) || empty($gateway->secret_key)) {
        wp_send_json_error('Configuration incomplète');
    }

    $payload = ['timestamp' => time()];
    $signature = Payment_Hub_HMAC::sign($payload, $gateway->secret_key);

    $args = [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode([
            'timestamp' => $payload['timestamp'],
            'signature' => $signature
        ]),
        'timeout' => 15
    ];

    if ($gateway->test_mode) {
        $args['sslverify'] = false;
    }

    $response = wp_remote_post(
        trailingslashit($gateway->hub_url) . 'api/ping',
        $args
    );

    if (is_wp_error($response)) {
        wp_send_json_error('Payment Hub injoignable : ' . $response->get_error_message());
    }

    $http_code = wp_remote_retrieve_response_code($response);
    $body_raw = wp_remote_retrieve_body($response);
    $body = json_decode($body_raw, true);

    if ($http_code !== 200) {
        $msg = "Erreur HTTP $http_code";
        if (!empty($body['error'])) $msg .= " : " . $body['error'];
        elseif (!empty($body['message'])) $msg .= " : " . $body['message'];
        wp_send_json_error($msg);
    }

    if (!isset($body['status']) || $body['status'] !== 'ok') {
        wp_send_json_error('Réponse invalide du Payment Hub');
    }

    wp_send_json_success('Connexion OK');
});
