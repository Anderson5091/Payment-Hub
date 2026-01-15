<?php
if (!defined('ABSPATH')) exit;

class WC_Gateway_Payment_Hub extends WC_Payment_Gateway {

    /** URL du Payment Hub */
    public string $hub_url = '';

    /** Clé secrète HMAC */
    public string $secret_key = '';

    public function __construct() {

        $this->id = 'payment_hub';
        $this->method_title = 'Payment Hub';
        $this->method_description = 'Paiement manuel via Payment Hub sécurisé';
        $this->has_fields = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->title       = (string) $this->get_option('title');
        $this->description = (string) $this->get_option('description');
        $this->hub_url     = (string) $this->get_option('hub_url');
        $this->secret_key  = (string) $this->get_option('secret_key');

        add_action(
            'woocommerce_update_options_payment_gateways_' . $this->id,
            [$this, 'process_admin_options']
        );
    }

    /**
     * Champs admin
     */
    public function init_form_fields() {

        $this->form_fields = [
            'enabled' => [
                'title'   => 'Activer',
                'type'    => 'checkbox',
                'label'   => 'Activer Payment Hub',
                'default' => 'yes'
            ],
            'title' => [
                'title'   => 'Titre affiché',
                'type'    => 'text',
                'default' => 'Paiement manuel (Payment Hub)'
            ],
            'description' => [
                'title'   => 'Description',
                'type'    => 'textarea',
                'default' => 'Payez en toute sécurité via notre Payment Hub.',
                'desc_tip' => true,
            ],
            'hub_url' => [
                'title'       => 'URL Payment Hub',
                'type'        => 'text',
                'description' => 'Ex: https://payment-site.com',
                'desc_tip'    => true
            ],
            'secret_key' => [
                'title'       => 'Clé secrète',
                'type'        => 'password',
                'description' => 'Clé partagée avec le Payment Hub',
                'desc_tip'    => true
            ],
        ];
    }

    /**
     * Traitement paiement
     */
    public function process_payment($order_id) {

        $order = wc_get_order($order_id);

        if (!$order) {
            return ['result' => 'error'];
        }

        $payload = [
            'order_id'     => $order_id,
            'amount'       => $order->get_total(),
            'currency'     => $order->get_currency(),
            'shop_domain'  => home_url(),
            'callback_url' => get_rest_url(null, 'payment/v1/confirm'),
        ];

        // Sign the request for the middleware
        $signature = Payment_Hub_HMAC::sign($payload, $this->secret_key);

        $response = wp_remote_post(trailingslashit($this->hub_url) . 'api/payment/init', [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Signature'  => $signature
            ],
            'body'    => json_encode($payload),
            'timeout' => 15
        ]);

        if (is_wp_error($response)) {
            wc_add_notice('Erreur de communication avec le Payment Hub.', 'error');
            return ['result' => 'error'];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($body['redirect_url'])) {
            wc_add_notice('Le Payment Hub a renvoyé une réponse invalide.', 'error');
            return ['result' => 'error'];
        }

        // Marquer la commande
        $order->update_status('on-hold', 'Redirection vers Payment Hub effectuée.');
        wc_empty_cart();

        return [
            'result'   => 'success',
            'redirect' => $body['redirect_url']
        ];
    }

    /**
     * Bouton test connexion
     */
    public function admin_options() {

        parent::admin_options();

        echo '<tr valign="top">
            <th scope="row">Tester la connexion</th>
            <td>
                <button id="payment-hub-test" class="button">Tester</button>
                <span id="payment-hub-test-result" style="margin-left:10px;"></span>
            </td>
        </tr>';
    }
}
