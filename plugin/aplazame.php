<?php
/*
 * Plugin Name: Aplazame
 * Plugin URI: https://github.com/aplazame/woocommerce
 * Version: 0.9.0
 * Description: Aplazame offers a payment method to receive funding for the purchases.
 * Author: Aplazame
 * Author URI: https://aplazame.com
 *
 * Text Domain: aplazame
 * Domain Path: /i18n/languages/
 *
 * WC requires at least: 2.3
 * WC tested up to: 3.4.3
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
	const VERSION = '0.9.0';
	const METHOD_ID = 'aplazame';
	const METHOD_TITLE = 'Aplazame';

	public static function _m_or_a( $obj, $method, $attribute ) {
		if ( method_exists( $obj, $method ) ) {
			return $obj->$method();
		}

		return $obj->$attribute;
	}

	public static function _m_or_m( $obj, $method1, $method2 ) {
		if ( method_exists( $obj, $method1 ) ) {
			return $obj->$method1();
		}

		return $obj->$method2();
	}

	/**
	 * @param string $msg
	 */
	public static function log( $msg ) {
		$log = new WC_Logger();
		$log->add( self::METHOD_ID, $msg );
	}

	public static function configure_aplazame_profile( $sandbox, $private_key ) {
		$client = new Aplazame_Sdk_Api_Client(
			getenv( 'APLAZAME_API_BASE_URI' ) ? getenv( 'APLAZAME_API_BASE_URI' ) : 'https://api.aplazame.com',
			($sandbox ? Aplazame_Sdk_Api_Client::ENVIRONMENT_SANDBOX : Aplazame_Sdk_Api_Client::ENVIRONMENT_PRODUCTION),
			$private_key
		);

		$response = $client->get( '/me',
			array(
				'confirmation_url' => '',
			)
		);

		return $response;
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
	 * @param string $apiBaseUri
	 */
	public function __construct( $apiBaseUri ) {

		// Dependencies
		include_once( 'classes/lib/Helpers.php' );

		register_uninstall_hook( __FILE__, 'WC_Aplazame_Install::uninstall' );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );

		// i18n
		load_plugin_textdomain( 'aplazame', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );

		// Settings
		register_activation_hook( __FILE__, 'WC_Aplazame_Install::resetSettings' );
		$this->settings = get_option( 'woocommerce_aplazame_settings' );
		if ( ! $this->settings ) {
			$this->settings = WC_Aplazame_Install::reset_settings();
		} else {
			$this->settings = array_merge( WC_Aplazame_Install::$defaultSettings, $this->settings );
		}
		$this->enabled         = $this->settings['enabled'] === 'yes';
		$this->sandbox         = $this->settings['sandbox'] === 'yes';
		$this->apiBaseUri      = $apiBaseUri;
		$this->private_api_key = $this->settings['private_api_key'];

		// Aplazame JS
		add_action( 'wp_head', array( $this, 'aplazameJs' ), 999999 );

		add_action( 'init', array( 'WC_Aplazame_Install', 'upgrade' ), 5 );
		register_activation_hook( __FILE__, 'WC_Aplazame_Install::upgrade' );

		// TODO: Redirect nav
		// add_filter('wp_nav_menu_objects', '?');
		// Widgets
		add_action( 'woocommerce_single_product_summary', array(
			$this,
			'product_widget',
		), 100 );

		add_action( 'woocommerce_after_cart_totals', array(
			$this,
			'cart_widget',
		), 100 );

		add_filter( 'woocommerce_product_data_tabs', array( $this, 'aplazame_campaigns_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'product_campaigns' ) );

		add_action( 'woocommerce_api_aplazame', array( $this, 'api_router' ) );
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
	 *
	 * @param  array $links
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_aplazame_gateway' ) . '">' . __( 'Settings', 'aplazame' ) . '</a>',
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
	 * @param string              $msg
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

	public function aplazameJs() {

		Aplazame_Helpers::render_to_template( 'layout/header.php' );
	}

	// Widgets
	public function is_product_widget_enabled() {
		return $this->enabled && $this->settings['product_widget_enabled'] == 'yes';
	}

	public function product_widget() {
		if ( ! $this->is_product_widget_enabled() ) {
			return;
		}

		Aplazame_Helpers::render_to_template( 'widgets/product.php' );
	}

	public function is_cart_widget_enabled() {
		return $this->enabled && $this->settings['cart_widget_enabled'] == 'yes';
	}

	public function cart_widget() {
		if ( ! $this->is_cart_widget_enabled() ) {
			return;
		}

		Aplazame_Helpers::render_to_template( 'widgets/cart.php' );
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

	public function api_router() {
		$path           = isset( $_GET['path'] ) ? $_GET['path'] : '';
		$pathArguments  = isset( $_GET['path_arguments'] ) ? json_decode( stripslashes_deep( $_GET['path_arguments'] ), true ) : array();
		$queryArguments = isset( $_GET['query_arguments'] ) ? json_decode( stripslashes_deep( $_GET['query_arguments'] ), true ) : array();
		$payload        = json_decode( file_get_contents( 'php://input' ), true );

		include_once( 'classes/api/Aplazame_Api_Router.php' );
		$api = new Aplazame_Api_Router( $this->private_api_key, $this->sandbox );

		$api->process( $path, $pathArguments, $queryArguments, $payload ); // die
	}
}

class WC_Aplazame_Install {
	const SETTINGS_KEY = 'woocommerce_aplazame_settings';

	const VERSION_KEY = 'aplazame_version';

	public static $defaultSettings = array(
		'cart_widget_enabled'             => 'yes',
		'enabled'                         => null,
		'sandbox'                         => 'yes',
		'button'                          => '#payment ul li:has(input#payment_method_aplazame)',
		'quantity_selector'               => '',
		'price_product_selector'          => '',
		'price_variable_product_selector' => '#main [itemtype="http://schema.org/Product"] .single_variation_wrap .amount',
		'product_widget_enabled'          => 'yes',
		'public_api_key'                  => '',
		'private_api_key'                 => '',
	);

	public static function upgrade() {
		if ( version_compare( get_option( self::VERSION_KEY ), WC_Aplazame::VERSION, '<' ) ) {
			self::set_aplazame_profile();
			self::remove_redirect_page();

			self::update_aplazame_version();
		}
	}

	public static function uninstall() {
		self::remove_settings();
	}

	/**
	 * @return array
	 */
	public static function reset_settings() {
		add_option( self::SETTINGS_KEY, self::$defaultSettings );

		return self::$defaultSettings;
	}

	public static function remove_settings() {
		delete_option( self::SETTINGS_KEY );
	}

	private static function update_aplazame_version() {
		delete_option( self::VERSION_KEY );
		add_option( self::VERSION_KEY, WC_Aplazame::VERSION );
	}

	private static function set_aplazame_profile() {
		/** @var WC_Aplazame $aplazame */
		global $aplazame;

		if ( ! $aplazame->private_api_key ) {
			return;
		}

		try {
			WC_Aplazame::configure_aplazame_profile( $aplazame->settings['sandbox'], $aplazame->private_api_key );
		} catch (Exception $e) {
			$aplazame->private_api_key = null;
			$aplazame->settings['private_api_key'] = null;
		}
	}

	private static function remove_redirect_page() {
		include_once( 'classes/lib/Redirect.php' );
		$redirect = new Aplazame_Redirect();
		$redirect->removeRedirectPage();
	}
}

$GLOBALS['aplazame'] = new WC_Aplazame( defined( 'APLAZAME_API_BASE_URI' ) ? APLAZAME_API_BASE_URI : 'https://api.aplazame.com' );
include_once( 'classes/wc-aplazame-proxy.php' );
