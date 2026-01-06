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

        $this->title      = $this->get_option('title');
        $this->hub_url    = (string) $this->get_option('hub_url');
        $this->secret_key = (string) $this->get_option('secret_key');

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

        $order->update_status(
            'on-hold',
            'En attente de validation du paiement via Payment Hub'
        );

        // 🔐 Génération token HMAC (conforme au document)
        $payload = implode('|', [
            $order_id,
            $order->get_total(),
            time()
        ]);

        $token = hash_hmac('sha256', $payload, $this->secret_key);

        $redirect_url = trailingslashit($this->hub_url) . 'pay?token=' . $token;

        return [
            'result'   => 'success',
            'redirect' => $redirect_url
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
