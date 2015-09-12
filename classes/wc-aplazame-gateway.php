<?php

if (!defined('ABSPATH')) {
    exit;
}


class WC_Aplazame_Gateway extends WC_Payment_Gateway
{
    protected $order = null;

    public function __construct()
    {
        $this->id = 'aplazame';
        $this->has_fields = true;
        $this->method_title = 'Aplazame';

        # Settings
        $this->init_form_fields();
        $this->init_settings();

        $this->title = 'Aplazame';
        $this->enabled = $this->settings['enabled'];
        $this->icon = plugins_url('assets/img/icon.png', dirname(__FILE__));

        add_action('woocommerce_update_options_payment_gateways', array(
            $this, 'process_admin_options'));

        add_action('woocommerce_update_options_payment_gateways_' .
            $this->id, array($this, 'process_admin_options'));

        # todo or Not todo
        # add_action('admin_notices', array($this, '?'));
    }

    public function is_available()
    {
        if (($this->enabled === 'no') ||
                (get_woocommerce_currency() !== 'EUR') ||
                (!$this->settings['public_api_key']) ||
                (!$this->settings['private_api_key'])) {
            return false;
        }

        return true;
    }

    public function payment_fields()
    {
        Aplazame_Helpers::render_to_template('gateway/payment-fields.php');
    }

    public function process_payment($order_id)
    {
        $url = get_permalink(Aplazame_Helpers::redirect_ID());

        return array(
            'result' => 'success',
            'redirect' => add_query_arg(array('order_id' => $order_id), $url)
        );
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'type' => 'checkbox',
                'title' => __('Enable/Disable', 'aplazame'),
                'label' => __('Enable Aplazame module', 'aplazame'),
                'default' => 'yes'
            ),
            'sandbox' => array(
                'type' => 'checkbox',
                'title' => 'Sandbox',
                'description' => __('Determines if the module is on Sandbox mode', 'aplazame'),
                'label' => __('Turn on Sandbox', 'aplazame'),
                'default' => 'yes'
            ),
            'host' => array(
                'type' => 'text',
                'title' => 'Host',
                'description' => __('Aplazame Host', 'aplazame'),
                'default' => 'https://aplazame.com'
            ),
            'api_version' => array(
                'type' => 'text',
                'title' => __('API Version', 'aplazame'),
                'description' => __('Aplazame API Version', 'aplazame'),
                'default' => 'v1.2'
            ),
            'button' => array(
                'type' => 'text',
                'title' => __('Button', 'aplazame'),
                'description' => __('Aplazame Button CSS Selector', 'aplazame'),
                'default' => '#payment ul li:has(input#payment_method_aplazame)'
            ),
            'public_api_key' => array(
                'type' => 'text',
                'title' => __('Public API Key', 'aplazame'),
                'description' => __('Aplazame Public Key', 'aplazame'),
                'default' => ''
            ),
            'private_api_key' => array(
                'type' => 'password',
                'title' => __('Private API Key', 'aplazame'),
                'description' => __('Aplazame Private Key', 'aplazame'),
                'default' => ''
            ),
            'enable_analytics' => array(
                'type' => 'checkbox',
                'title' => __('Enable/Disable', 'aplazame'),
                'label' => __('Enable Aplazame Analytics', 'aplazame'),
                'default' => 'yes'
            ),
        );
    }
}
