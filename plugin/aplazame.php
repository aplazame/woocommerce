<?php
/*
 * Plugin Name: Aplazame
 * Plugin URI: https://github.com/aplazame/woocommerce
 * Version: 0.6.1
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

include_once( 'lib/Aplazame/Sdk/autoload.php' );
include_once( 'lib/Aplazame/Aplazame/autoload.php' );

class WC_Aplazame {
	const VERSION = '0.6.1';
	const METHOD_ID = 'aplazame';
	const METHOD_TITLE = 'Aplazame';

	/**
	 * @param string $msg
	 */
	public static function log( $msg ) {
		$log = new WC_Logger();
		$log->add( self::METHOD_ID, $msg );
	}

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
		include_once( 'classes/lib/Helpers.php' );
		include_once( 'classes/lib/Redirect.php' );

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

		add_filter( 'woocommerce_product_data_tabs', array( $this, 'aplazame_campaigns_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'product_campaigns' ) );
	}

	public function aplazame_campaigns_tab( $tabs ) {
		$tabs['aplazame_campaigns'] = array(
			'label' => __( 'Aplazame Campaigns', 'aplazame' ),
			'target' => 'aplazame_campaigns_tab',
		);

		return $tabs;
	}

	public function product_campaigns() {
		Aplazame_Helpers::render_to_template( 'product/campaigns.php' );
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
	 * @return Aplazame_Client
	 */
	public function get_client() {
		include_once( 'classes/sdk/Client.php' );

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
			case 'aplazame_api':
				$path           = isset( $_GET['path'] ) ? $_GET['path'] : '';
				$pathArguments  = isset( $_GET['path_arguments'] ) ? json_decode( stripslashes_deep( $_GET['path_arguments'] ), true ) : array();
				$queryArguments = isset( $_GET['query_arguments'] ) ? json_decode( stripslashes_deep( $_GET['query_arguments'] ), true ) : array();

				include_once( 'classes/api/Aplazame_Api_Router.php' );
				$api = new Aplazame_Api_Router( $this->private_api_key );

				$api->process( $path, $pathArguments, $queryArguments ); // die
				break;
			case 'confirm':
				return $this->confirm();
			case 'history':
				return $this->history();
		}

		return $template;
	}

	public function confirm() {

		$order_id = $_GET['order_id'];
		$order = new WC_Order( $order_id );

		$client = $this->get_client();

		try {
			$aOrder = $client->fetch( $order_id );
			if ( $aOrder['total_amount'] !== Aplazame_Sdk_Serializer_Decimal::fromFloat( $order->get_total() )->jsonSerialize()
			     || $aOrder['currency']['code'] !== $order->get_order_currency()
			) {
				status_header( 403 );
				return null;
			}

			$client->authorize( $order_id );
		} catch ( Exception $e ) {
			$order->update_status( 'failed',
				sprintf( __( '%s ERROR: Order #%s cannot be confirmed. Reason: %s', 'aplazame' ),
					self::METHOD_TITLE,
					$order_id,
					$e->getMessage()
				) );

			status_header( 500 );

			return null;
		}

		$order->update_status( 'processing', sprintf( __( 'Confirmed by %s.', 'aplazame' ), $this->apiBaseUri ) );
		status_header( 204 );

		return null;
	}

	public function history() {
		include_once( 'classes/api/Aplazame_Api_Router.php' );
		if ( ! Aplazame_Api_Router::verify_authentication( $this->private_api_key ) ) {
			status_header( 403 );
			return null;
		}

		$order = wc_get_order( $_GET['order_id'] );
		if ( ! $order ) {
			status_header( 404 );
			return null;
		}

		/** @var WP_Post[] $wcOrders */
		$wcOrders = get_posts( array(
			'meta_key'    => '_billing_email',
			'meta_value'  => $order->billing_email,
			'post_type'   => 'shop_order',
			'numberposts' => - 1,
		) );

		$historyOrders = array();

		foreach ( $wcOrders as $wcOrder ) {
			$historyOrders[] = Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder::createFromOrder( new WC_Order( $wcOrder->ID ) );
		}

		wp_send_json( $historyOrders );
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
		} catch ( Exception $e ) {
			$this->add_order_note( $order_id,
				sprintf( __( '%s ERROR: Order #%s cannot be cancelled. Reason: %s', 'aplazame' ),
					self::METHOD_TITLE,
					$order_id,
					$e->getMessage()
				) );

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
