<?php
/**
 * Plugin Name: WooCommerce Payment Hub
 * Description: Paiement manuel sécurisé via Payment Hub externe.
 * Version: 1.0.0
 * Author: Payment Hub Team
 */
if (!defined('ABSPATH')) exit;
add_action('plugins_loaded', function() {
    if (!class_exists('WC_Payment_Gateway')) return;

    require_once plugin_dir_path(__FILE__) . 'includes/class-gateway-payment-hub.php';

    add_filter('woocommerce_payment_gateways', function ($methods) {
        $methods[] = 'WC_Gateway_Payment_Hub';
        return $methods;
    });
});
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'woocommerce_page_wc-settings') return;
    wp_enqueue_script('payment-hub-admin', plugin_dir_url(__FILE__) . 'assets/admin.js', ['jquery'], '1.0', true);
    wp_localize_script('payment-hub-admin', 'PaymentHub', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('payment_hub_test')
    ]);
});
add_action('wp_ajax_payment_hub_test_connection', function () {
    check_ajax_referer('payment_hub_test', 'nonce');
    $gateway = new WC_Gateway_Payment_Hub();
    if (!$gateway->hub_url || !$gateway->secret_key) wp_send_json_error('Configuration incomplète');
    $payload = ['timestamp' => time()];
    $signature = hash_hmac('sha256', json_encode($payload), $gateway->secret_key);
    $response = wp_remote_post(trailingslashit($gateway->hub_url) . 'api/ping', [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode(['timestamp' => $payload['timestamp'], 'signature' => $signature])
    ]);
    if (is_wp_error($response)) wp_send_json_error('Hub injoignable');
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!isset($body['status']) || $body['status'] !== 'ok') wp_send_json_error('Réponse invalide');
    wp_send_json_success();
});
