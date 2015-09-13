<?php
/*
 * Plugin Name: Aplazame
 * Plugin URI: https://github.com/aplazame/woocommerce
 * Version: 0.0.1
 * Description: Aplazame offers a payment method to receive funding for the purchases.
 * Author: calvin
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}


class WC_Aplazame
{
    const VERSION = '0.0.1';
    const METHOD_ID = 'aplazame';
    const METHOD_TITLE = 'Aplazame';

    public function __construct()
    {
        # Dependencies
        include_once('classes/lib/Filters.php');
        include_once('classes/lib/Helpers.php');
        include_once('classes/lib/Redirect.php');

        # Sdk
        include_once('classes/sdk/Client.php');
        include_once('classes/sdk/Serializers.php');

        # Hooks: Gateway
        add_filter('woocommerce_payment_gateways', array($this, 'add_gateway'));

        # Hooks: Analytics
        add_filter('woocommerce_integrations', array($this, 'add_analytics'));

        # l10n
        load_plugin_textdomain('aplazame', false, dirname(
            plugin_basename(__FILE__)) . '/l10n/es');

        # Settings
        $this->settings = get_option('woocommerce_aplazame_settings', array());
        $this->sandbox = $this->settings['sandbox'] === 'yes';
        $this->host = $this->settings['host'];
        $this->private_api_key = $this->settings['private_api_key'];

        # Redirect
        register_activation_hook(__FILE__, 'Aplazame_Redirect::redirect_ID');
        add_action('wp_footer', 'Aplazame_Redirect::payload');

        # TODO: Redirect nav
        #add_filter('wp_nav_menu_objects', '?');

        # Router to action
        add_filter('template_include', array($this, 'router'));

        # Widgets
        add_action('woocommerce_single_product_summary', array(
            $this, 'simulator'), 100);

        # Handlers
        add_action('woocommerce_order_status_cancelled', array(
            $this, 'order_cancelled'));

        add_action('woocommerce_order_status_refunded', array(
            $this, 'order_cancelled'));
    }

    public function log($msg)
    {
        if ($this->sandbox) {
            $log = new WC_Logger();
            $log->add(self::METHOD_ID, $msg);
        }
    }

    public function get_client()
    {
        return new Aplazame_Client(
            $this->host,
            $this->settings['api_version'],
            $this->sandbox,
            $this->private_api_key);
    }

    public function add_order_note($order_id, $msg)
    {
        $order = new WC_Order($order_id);
        $order->add_order_note($msg);
    }

    protected function is_private_key_verified()
    {
        return ($this->private_api_key !== '') && (substr($_SERVER[
            'HTTP_AUTHORIZATION'], 7) === $this->private_api_key);
    }

    # Settings
    public function add_gateway($methods)
    {
        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }

        include_once('classes/wc-aplazame-gateway.php');

        $methods[] = 'WC_Aplazame_Gateway';
        return $methods;
    }

    public function add_analytics($integrations)
    {
        if (!class_exists('WC_Integration')) {
            return;
        }

        include_once('classes/wc-aplazame-analytics.php');

        $integrations[] = 'WC_Aplazame_Analytics';
        return $integrations;
    }

    # Controllers
    public function router($template)
    {
        if (Aplazame_Redirect::is_redirect()) {
            switch ($_GET['action']) {
                case 'confirm':
                    return $this->confirm();
                case 'history':
                    return $this->history();
            }
        }

        return $template;
    }

    public function confirm()
    {
        $order = new WC_Order($_GET['order_id']);

        try {
            $body = $this->get_client()->authorize($order->id);

        } catch (Aplazame_Exception $e) {
            $this->add_order_note($order->id, sprintf(
                __('%s ERROR:  Order #%s cannot be confirmed.', 'aplazame'),
                self::METHOD_TITLE, $order->id));

            status_header($e->get_status_code());
            return null;
        }

        if ($body->amount === Aplazame_Filters::decimals($order->get_total())) {
            $order->update_status('processing', sprintf(
                __('Confirmed by %s.', 'aplazame'), $this->host));

            status_header(204);
        } else {
            status_header(403);
        }

        return null;
    }

    public function history()
    {
        $order = new WC_Order($_GET['order_id']);

        if (static::is_aplazame_order($order->id) &&
                $this->is_private_key_verified()) {
            $serializers = new Aplazame_Serializers();

            $qs = get_posts(array(
                'meta_key' => '_billing_email',
                'meta_value' => $order->billing_email,
                'post_type' => 'shop_order',
                'numberposts'=> -1
            ));

            return wp_send_json($serializers->get_history($qs));
        }

        status_header(403);
        return null;
    }

    # Widgets
    public function simulator()
    {
        Aplazame_Helpers::render_to_template('widgets/simulator.php');
    }

    # Handlers (no return)
    public function order_cancelled($order_id)
    {
        if (static::is_aplazame_order($order_id)) {
            try {
                $this->get_client()->cancel($order_id);

                $this->add_order_note($order_id, sprintf(
                    __('Order #%s has been successful cancelled by %s.', 'aplazame'),
                    $order_id, self::METHOD_TITLE));

            } catch (Aplazame_Exception $e) {
                $this->add_order_note($order_id, sprintf(
                    __('%s ERROR:  Order #%s cannot be cancelled.', 'aplazame'),
                    self::METHOD_TITLE, $order_id));
            }
        }
    }

    # Static
    protected static function is_aplazame_order($order_id)
    {
        return Aplazame_Helpers::get_payment_method($order_id) === self::METHOD_ID;
    }
}

$GLOBALS['aplazame'] = new WC_Aplazame();
