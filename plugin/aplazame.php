<?php
/*
 * Plugin Name: Aplazame
 * Plugin URI: https://github.com/aplazame/woocommerce
 * Version: 0.3.0
 * Description: Aplazame offers a payment method to receive funding for the purchases.
 * Author: Aplazame
 * Author URI: https://aplazame.com
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Aplazame {
	const VERSION = '0.3.0';
	const METHOD_ID = 'aplazame';
	const METHOD_TITLE = 'Aplazame';
	/**
	 * @var array
	 */
	public $settings;
	/**
	 * @var null|bool Null when the plugin is not configured yet.
	 */
	public $enabled;
	/**
	 * @var bool
	 */
	public $sandbox;
	/**
	 * @var string
	 */
	public $apiBaseUri;

	/**
	 * @var Aplazame_Redirect
	 */
	public $redirect;

	/**
	 * @param string $apiBaseUri
	 */
	public function __construct($apiBaseUri) {

		// Dependencies
		include_once( 'classes/lib/Filters.php' );
		include_once( 'classes/lib/Helpers.php' );
		include_once( 'classes/lib/Redirect.php' );

		// Sdk
		include_once( 'classes/sdk/Client.php' );
		include_once( 'classes/sdk/Serializers.php' );

		register_uninstall_hook( __FILE__, 'WC_Aplazame_Install::uninstall' );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );

		// l10n
		load_plugin_textdomain( 'aplazame', false, dirname( plugin_basename( __FILE__ ) ) . '/l10n/es' );

		// Settings
		register_activation_hook( __FILE__, 'WC_Aplazame_Install::resetSettings' );
		$this->settings = get_option( 'woocommerce_aplazame_settings' );
		if ( ! $this->settings ) {
			$this->settings = WC_Aplazame_Install::resetSettings();
		} else {
			$this->settings = array_merge(WC_Aplazame_Install::$defaultSettings, $this->settings);
		}
		$this->enabled         = $this->settings['enabled'] === 'yes';
		$this->sandbox         = $this->settings['sandbox'] === 'yes';
		$this->apiBaseUri      = $apiBaseUri;
		$this->private_api_key = $this->settings['private_api_key'];

		// Aplazame JS
		add_action( 'wp_head', array( $this, 'aplazameJs' ), 999999 );

		// Redirect
		$this->redirect = new Aplazame_Redirect();
		register_activation_hook( __FILE__, array( $this->redirect, 'addRedirectPage' ) );
		register_deactivation_hook( __FILE__, array( $this->redirect, 'removeRedirectPage' ) );
		add_action( 'wp_footer', array( $this->redirect, 'checkout' ) );

		// TODO: Redirect nav
		// add_filter('wp_nav_menu_objects', '?');
		// Router to action
		add_filter( 'template_include', array( $this, 'router' ) );

		// Widgets
		add_action( 'woocommerce_single_product_summary', array(
			$this,
			'product_widget',
		), 100 );

		add_action( 'woocommerce_after_cart_totals', array(
			$this,
			'cart_widget',
		), 100 );

		// Handlers
		add_action( 'woocommerce_order_status_cancelled', array(
			$this,
			'order_cancelled',
		) );

		add_action( 'woocommerce_order_status_refunded', array(
			$this,
			'order_cancelled',
		) );
	}

	/**
	 * Add relevant links to plugins page
	 * @param  array $links
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_aplazame_gateway') . '">' . __( 'Settings', 'aplazame' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * @param string $msg
	 */
	public function log( $msg ) {
		if ( $this->sandbox ) {
			$log = new WC_Logger();
			$log->add( self::METHOD_ID, $msg );
		}
	}

	/**
	 * @return Aplazame_Client
	 */
	public function get_client() {

		return new Aplazame_Client( $this->apiBaseUri, $this->sandbox,
			$this->private_api_key );
	}

	/**
	 * @param int|object|WC_Order $order_id .
	 * @param string $msg
	 */
	public function add_order_note( $order_id, $msg ) {
		$order = new WC_Order( $order_id );
		$order->add_order_note( $msg );
	}

	/**
	 * @return bool
	 */
	protected function is_private_key_verified() {

		return ( $this->private_api_key !== '' ) &&
		       ( substr( $_SERVER['HTTP_AUTHORIZATION'], 7 ) === $this->private_api_key );
	}

	// Hooks
	/**
	 * @param array $methods
	 *
	 * @return array|void
	 */
	public function add_gateway( $methods ) {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		include_once( 'classes/wc-aplazame-gateway.php' );

		$methods[] = 'WC_Aplazame_Gateway';

		return $methods;
	}

	// Controllers
	/**
	 * @param string $template
	 *
	 * @return null|string
	 */
	public function router( $template ) {
		if (! isset( $_GET['action'] ) || ! $this->redirect->isRedirect(get_the_ID())) {
			return $template;
		}

		switch ( $_GET['action'] ) {
			case 'confirm':
				return $this->confirm();
			case 'history':
				return $this->history();
		}

		return $template;
	}

	public function confirm() {

		$order = new WC_Order( $_GET['order_id'] );

		$client = $this->get_client();

		try {
			$body = $client->authorize( $order->id );
		} catch ( Aplazame_Exception $e ) {
			$order->update_status( 'failed',
				sprintf( __( '%s ERROR: Order #%s cannot be confirmed.', 'aplazame' ), self::METHOD_TITLE,
					$order->id ) );

			status_header( $e->get_status_code() );

			return null;
		}

		if ( $body->amount === Aplazame_Filters::decimals( $order->get_total() ) ) {
			$order->update_status( 'processing', sprintf( __( 'Confirmed by %s.', 'aplazame' ), $this->apiBaseUri ) );

			status_header( 204 );
		} else {
			status_header( 403 );
		}

		return null;
	}

	public function history() {

		$order = new WC_Order( $_GET['order_id'] );

		if ( static::is_aplazame_order( $order->id ) && $this->is_private_key_verified() ) {
			$serializers = new Aplazame_Serializers();

			$qs = get_posts( array(
				'meta_key'    => '_billing_email',
				'meta_value'  => $order->billing_email,
				'post_type'   => 'shop_order',
				'numberposts' => - 1,
			) );

			wp_send_json( $serializers->get_history( $qs ) );

			return null;
		}

		status_header( 403 );

		return null;
	}

	public function aplazameJs() {

		Aplazame_Helpers::render_to_template( 'layout/header.php' );
	}

	// Widgets
	public function product_widget() {

		Aplazame_Helpers::render_to_template( 'widgets/product.php' );
	}

	public function cart_widget() {

		Aplazame_Helpers::render_to_template( 'widgets/cart.php' );
	}

	// Handlers (no return)
	/**
	 * @param int $order_id
	 */
	public function order_cancelled( $order_id ) {
		if ( ! static::is_aplazame_order( $order_id ) ) {
			return;
		}

		$client = $this->get_client();

		try {
			$client->cancel( $order_id );
		} catch ( Aplazame_Exception $e ) {
			$this->add_order_note( $order_id,
				sprintf( __( '%s ERROR: Order #%s cannot be cancelled.', 'aplazame' ), self::METHOD_TITLE,
					$order_id ) );

			return;
		}

		$this->add_order_note( $order_id,
			sprintf( __( 'Order #%s has been successful cancelled by %s.', 'aplazame' ), $order_id,
				self::METHOD_TITLE ) );
	}

	// Static
	/**
	 * @param int $order_id
	 *
	 * @return bool
	 */
	protected static function is_aplazame_order( $order_id ) {
		return Aplazame_Helpers::get_payment_method( $order_id ) === self::METHOD_ID;
	}
}

class WC_Aplazame_Install {
	public static $defaultSettings = array(
		'enabled'                         => null,
		'sandbox'                         => 'yes',
		'button'                          => '#payment ul li:has(input#payment_method_aplazame)',
		'quantity_selector'               => '',
		'price_product_selector'          => '',
		'price_variable_product_selector' => '#main [itemtype="http://schema.org/Product"] .single_variation_wrap .amount',
		'public_api_key'                  => '',
		'private_api_key'                 => '',
	);

	public static function uninstall() {
		self::removeSettings();
	}

	/**
	 * @return array
	 */
	public static function resetSettings() {
		add_option( 'woocommerce_aplazame_settings', self::$defaultSettings );

		return self::$defaultSettings;
	}

	public static function removeSettings() {
		delete_option( 'woocommerce_aplazame_settings' );
	}
}

$GLOBALS['aplazame'] = new WC_Aplazame( defined( 'APLAZAME_API_BASE_URI' ) ? APLAZAME_API_BASE_URI : 'https://api.aplazame.com' );
include_once( 'classes/wc-aplazame-proxy.php' );
