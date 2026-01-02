<?php
if (!defined('ABSPATH')) exit;
class WC_Gateway_Payment_Hub extends WC_Payment_Gateway {
    public function __construct() {
        $this->id = 'payment_hub';
        $this->method_title = 'Payment Hub';
        $this->method_description = 'Paiement manuel via Payment Hub';
        $this->has_fields = false;
        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->get_option('title');
        $this->hub_url = $this->get_option('hub_url');
        $this->secret_key = $this->get_option('secret_key');
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this,'process_admin_options']);
    }
    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => ['title'=>'Activer','type'=>'checkbox','default'=>'yes'],
            'title' => ['title'=>'Titre','type'=>'text','default'=>'Paiement manuel (Payment Hub)'],
            'hub_url' => ['title'=>'URL Payment Hub','type'=>'text'],
            'secret_key' => ['title'=>'Clé secrète','type'=>'password']
        ];
    }
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $order->update_status('on-hold','En attente de paiement (Payment Hub)');
        return ['result'=>'success','redirect'=>$this->hub_url.'/pay?order_id='.$order_id];
    }
    public function admin_options() {
        parent::admin_options();
        echo '<tr><th>Tester la connexion</th><td><button id="payment-hub-test" class="button">Tester la connexion</button> <span id="payment-hub-test-result"></span></td></tr>';
    }
}
