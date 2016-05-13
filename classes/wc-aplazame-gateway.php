<?php

if (!defined('ABSPATH')) {
    exit;
}


class WC_Aplazame_Gateway extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = WC_Aplazame::METHOD_ID;
        $this->method_title = WC_Aplazame::METHOD_TITLE;
        $this->has_fields = true;

        # Settings
        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->method_title;
        $this->enabled = $this->settings['enabled'];
        $this->icon = plugins_url('assets/img/icon.png', dirname(__FILE__));

        $this->supports = array(
            'products',
            'refunds'
        );

        add_action('woocommerce_update_options_payment_gateways', array(
            $this, 'process_admin_options'));

        add_action('woocommerce_update_options_payment_gateways_' .
            $this->id, array($this, 'process_admin_options'));

        add_action('admin_notices', array($this, 'checks'));
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
        $url = get_permalink(Aplazame_Redirect::get_the_ID());
        WC()->session->redirect_order_id = $order_id;

        return array(
            'result' => 'success',
            'redirect' => add_query_arg(array('order_id' => $order_id), $url)
        );
    }

    public function process_refund($order_id, $amount=null, $reason='')
    {
        if ($amount) {
            /** @var WC_Aplazame $aplazame */
            global $aplazame;

            try {
                $aplazame->get_client()->refund(
                    $order_id, Aplazame_Filters::decimals($amount), $reason);

                $aplazame->add_order_note($order_id, sprintf(
                    __('%s has successfully returned %d %s of the order #%s.', 'aplazame'),
                    $this->method_title, $amount, get_woocommerce_currency(), $order_id));

                return true;
            } catch (Aplazame_Exception $e) {
                return new WP_Error('aplazame_refund_error', sprintf(
                    __('%s Error: "%s"', 'aplazame'),
                    $this->method_title, $e->get_field_error('amount')));
            }
        }

        return false;
    }

    public function checks()
    {
        if (!$this->enabled) {
            return;
        }

        $_render_to_notice = function($msg) {
            echo '<div class="error"><p>' . $msg . '</p></div>';
        };

        if (!$this->settings['public_api_key'] || !$this->settings['private_api_key']) {
            $_render_to_notice(sprintf(__(
                'Aplazame gateway requires the API keys, please ' .
                '<a href="%s">contact us</a> and take your keys.', 'aplazame'),
                'mailto:soporte.woo@aplazame.com?subject=i-need-a-token'));
        }
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
                'description' => __(
                    'Determines if the module is on Sandbox mode', 'aplazame'),
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
