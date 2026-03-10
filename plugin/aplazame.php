<?php //phpcs:ignore
/**
 * Plugin Name: Aplazame
 * Plugin URI: https://github.com/aplazame/woocommerce
 * Version: 4.2.0
 * Description: Aplazame offers a payment method to receive funding for the purchases.
 * Author: Aplazame
 * Author URI: https://aplazame.com
 *
 * Text Domain: aplazame
 * Domain Path: /i18n/languages/
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 10.2.1
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WC_Aplazame
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'lib/Aplazame/Sdk/autoload.php';
require_once 'lib/Aplazame/Aplazame/autoload.php';

/** Aplazame main class */
class WC_Aplazame {
	const VERSION      = '4.2.0';
	const METHOD_ID    = 'aplazame';
	const METHOD_TITLE = 'Aplazame';

	/**
	 * Private API key
	 *
	 * @var mixed
	 */
	private mixed $private_api_key;

	/**
	 * Method or attribute function for retro-compatibility
	 *
	 * @param mixed  $obj Object to use.
	 * @param string $method Method name.
	 * @param string $attribute Attribute name.
	 *
	 * @return mixed
	 */
	public static function method_or_attribute( mixed $obj, string $method, string $attribute ): mixed {
		if ( method_exists( $obj, $method ) ) {
			return $obj->$method();
		}

		return $obj->$attribute;
	}

	/**
	 * Method availability function for retro-compatibility
	 *
	 * @param mixed  $obj Object to use.
	 * @param string $method1 First method name.
	 * @param string $method2 Second method name.
	 *
	 * @return mixed
	 */
	public static function method1_or_method2( mixed $obj, string $method1, string $method2 ): mixed {
		if ( method_exists( $obj, $method1 ) ) {
			return $obj->$method1();
		}

		return $obj->$method2();
	}

	/**
	 * Log function
	 *
	 * @param string $msg Log message.
	 */
	public static function log( string $msg ): void {
		$log = new WC_Logger();
		$log->add( self::METHOD_ID, $msg );
	}

	/**
	 * Configuration function
	 *
	 * @param mixed  $sandbox Sandbox mode.
	 * @param string $private_key Private API key.
	 *
	 * @return array
	 */
	public static function configure_aplazame_profile( mixed $sandbox, string $private_key ): array {

		global $aplazame;

		$client = new Aplazame_Sdk_Api_Client(
			$aplazame->api_base_uri,
			( $sandbox ? Aplazame_Sdk_Api_Client::ENVIRONMENT_SANDBOX : Aplazame_Sdk_Api_Client::ENVIRONMENT_PRODUCTION ),
			$private_key
		);

		return $client->get( '/merchants/api-keys' );
	}

	/**
	 * Settings var
	 *
	 * @var array
	 */
	public array $settings;
	/**
	 * Plugin enabled var
	 *
	 * @var null|bool Null when the plugin is not configured yet.
	 */
	public ?bool $enabled;
	/**
	 * Sandbox mode
	 *
	 * @var mixed
	 */
	public mixed $sandbox;

	/**
	 * API base URI var
	 *
	 * @var string
	 */
	public string $api_base_uri;

	/**
	 * Construct
	 *
	 * @param string $api_base_uri API base URI var.
	 */
	public function __construct( string $api_base_uri ) {

		/** Dependencies */
		include_once 'classes/lib/class-aplazame-helpers.php';

		register_uninstall_hook( __FILE__, 'WC_Aplazame_Install::uninstall' );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );

		/** Languages (i18n) */
		load_plugin_textdomain( 'aplazame', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );

		/** Settings */
		register_activation_hook( __FILE__, 'WC_Aplazame_Install::reset_settings' );
		$this->settings = get_option( 'woocommerce_aplazame_settings' );
		if ( ! $this->settings ) {
			$this->settings = WC_Aplazame_Install::reset_settings();
		} else {
			$this->settings = array_merge( WC_Aplazame_Install::$default_settings, $this->settings );
		}
		$this->enabled         = 'yes' === $this->settings['enabled'];
		$this->sandbox         = 'yes' === $this->settings['sandbox'];
		$this->api_base_uri    = $api_base_uri;
		$this->private_api_key = $this->settings['private_api_key'];

		/** Aplazame JS */
		add_action( 'wp_head', array( $this, 'aplazame_js' ), 999999 );

		add_action( 'init', array( 'WC_Aplazame_Install', 'upgrade' ), 5 );
		register_activation_hook( __FILE__, 'WC_Aplazame_Install::upgrade' );

		/** TODO: Redirect nav
		* add_filter('wp_nav_menu_objects', '?');
		*/

		/** Widgets */
		if ( $this->is_product_widget_enabled() ) {
			add_action(
				$this->settings['product_widget_action'],
				array(
					$this,
					'product_widget',
				),
				100
			);
		}

		if ( $this->is_cart_widget_enabled() ) {
			add_action(
				$this->settings['cart_widget_action'],
				array(
					$this,
					'cart_widget',
				),
				100
			);
		}

		add_filter( 'woocommerce_product_data_tabs', array( $this, 'aplazame_campaigns_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'product_campaigns' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'capture_order' ) );

		add_action( 'woocommerce_api_aplazame', array( $this, 'api_router' ) );

		/** Cart and Checkout Blocks */
		add_action( 'woocommerce_blocks_loaded', array( $this, 'add_gateway_block' ) );
		add_action(
			'before_woocommerce_init',
			function () {
				if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
				}
			}
		);

		/** Declare HPOS compatibility */
		add_action(
			'before_woocommerce_init',
			function () {
				if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
					\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
				}
			}
		);
	}

	/**
	 * Campaigns tabs
	 *
	 * @param mixed $tabs .
	 *
	 * @return mixed
	 */
	public function aplazame_campaigns_tab( mixed $tabs ): mixed {
		$tabs['aplazame_campaigns'] = array(
			'label'  => __( 'Aplazame Campaigns', 'aplazame' ),
			'target' => 'aplazame_campaigns_tab',
		);

		return $tabs;
	}

	/**
	 * Campaigns render on products
	 *
	 * @return void
	 */
	public function product_campaigns(): void {
		Aplazame_Helpers::render_to_template( 'product/campaigns.php' );
	}

	/**
	 * Order capture for pay later
	 *
	 * @param mixed $order_id .
	 *
	 * @return array|Exception|false
	 */
	public function capture_order( mixed $order_id ): false|Exception|array {

		$order = wc_get_order( $order_id );
		if ( self::method_or_attribute( $order, 'get_payment_method', 'payment_method' ) !== self::METHOD_ID ) {
			return false;
		}

		global $aplazame;

		$client = $aplazame->get_client()->api_client;

		try {
			$payload = $client->get( '/orders/' . $order_id . '/captures' );
		} catch ( Exception $e ) {
			return $e;
		}

		if ( 0 !== $payload['remaining_capture_amount'] ) {
			try {
				$response = $client->post( '/orders/' . $order_id . '/captures', array( 'amount' => $payload['remaining_capture_amount'] ) );
			} catch ( Exception $e ) {
				return $e;
			}

			return $response;
		}

		return false;
	}

	/**
	 * Add relevant links to plugins page
	 *
	 * @param array $links .
	 *
	 * @return array
	 * @throws Exception .
	 */
	public function plugin_action_links( array $links ): array {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=aplazame' ) . '">' . __( 'Settings', 'aplazame' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 * Client
	 *
	 * @return Aplazame_Client
	 */
	public function get_client(): Aplazame_Client {
		include_once 'classes/sdk/class-aplazame-client.php';

		return new Aplazame_Client( $this->api_base_uri, $this->sandbox, $this->private_api_key );
	}

	/**
	 * Order note
	 *
	 * @param mixed  $order_id .
	 * @param string $msg .
	 */
	public function add_order_note( mixed $order_id, string $msg ): void {
		$order = new WC_Order( $order_id );
		$order->add_order_note( $msg );
	}

	/** Hooks */

	/**
	 * Gateway
	 *
	 * @param array $methods .
	 *
	 * @return array|void
	 * @throws Exception .
	 */
	public function add_gateway( array $methods ) {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		include_once 'classes/class-wc-aplazame-gateway.php';
		$methods[] = 'WC_Aplazame_Gateway';

		return $methods;
	}

	/**
	 * Gateway block
	 *
	 * @return void
	 */
	public function add_gateway_block(): void {
		if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			return;
		}

		include_once 'classes/class-wc-aplazame-gateway-blocks-support.php';
		add_action(
			'woocommerce_blocks_payment_method_type_registration',
			function ( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
				$payment_method_registry->register( new WC_Aplazame_Gateway_Blocks_Support() );
			}
		);
	}

	/**
	 * Render aplazame.js
	 *
	 * @return void
	 */
	public function aplazame_js(): void {

		Aplazame_Helpers::render_to_template( 'layout/header.php' );
	}

	/** Widgets */

	/**
	 * Product widget on/off
	 *
	 * @return bool
	 */
	public function is_product_widget_enabled(): bool {
		return $this->enabled && 'disabled' !== $this->settings['product_widget_action'];
	}

	/**
	 * Product widget render
	 *
	 * @return void
	 */
	public function product_widget(): void {
		if ( ! $this->is_product_widget_enabled() ) {
			return;
		}

		Aplazame_Helpers::render_to_template( 'widgets/product.php' );
	}

	/**
	 * Cart widget on/off
	 *
	 * @return bool
	 */
	public function is_cart_widget_enabled(): bool {
		return $this->enabled && 'disabled' !== $this->settings['cart_widget_action'];
	}

	/**
	 * Cart widget render
	 *
	 * @return void
	 */
	public function cart_widget(): void {
		if ( ! $this->is_cart_widget_enabled() ) {
			return;
		}

		Aplazame_Helpers::render_to_template( 'widgets/cart.php' );
	}

	/** API */
	public function api_router(): void {
		$path            = isset( $_GET['path'] ) ? wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['path'] ) ) ) : '';
		$query_arguments = $_GET;
		$payload         = json_decode( file_get_contents( 'php://input' ), true );

		include_once 'classes/api/class-aplazame-api-router.php';
		$api = new Aplazame_Api_Router( $this->private_api_key, $this->sandbox );

		$api->process( $path, $query_arguments, $payload );
	}
}

/** Aplazame install class */
class WC_Aplazame_Install { //phpcs:ignore
	const SETTINGS_KEY = 'woocommerce_aplazame_settings';

	const VERSION_KEY = 'aplazame_version';

	/**
	 * Default settings
	 *
	 * @var array
	 */
	public static array $default_settings = array(
		'enabled'                         => null,
		'sandbox'                         => 'yes',
		'button'                          => '#payment ul li:has(input#payment_method_aplazame)',
		'quantity_selector'               => '',
		'price_product_selector'          => '',
		'price_variable_product_selector' => '#main [itemtype="http://schema.org/Product"] .single_variation_wrap .amount',
		'public_api_key'                  => '',
		'private_api_key'                 => '',
		'button_image'                    => 'https://cdn.aplazame.com/static/img/buttons/aplazame-blended-button-227px.png',
		'product_widget_action'           => 'woocommerce_single_product_summary',
		'cart_widget_action'              => 'woocommerce_after_cart_totals',
		'product_legal_advice'            => 'yes',
		'cart_legal_advice'               => 'yes',
		'title'                           => '',
		'description'                     => 'Compra primero y paga después con <a href="https://aplazame.com" target="_blank">Aplazame</a>.',
		'product_default_instalments'     => '',
		'cart_default_instalments'        => '',
		'product_widget_primary_color'    => '#334bff',
		'cart_widget_primary_color'       => '#334bff',
		'product_widget_layout'           => 'horizontal',
		'cart_widget_layout'              => 'horizontal',
		'product_widget_border'           => 'yes',
		'product_widget_align'            => 'center',
		'cart_widget_align'               => 'center',
		'product_pay_in_4'                => 'no',
		'cart_pay_in_4'                   => 'no',
		'widget_out_of_limits'            => 'show',
		'product_downpayment_info'        => 'yes',
		'cart_downpayment_info'           => 'yes',
		'product_widget_max_desired'      => 'no',
		'cart_widget_max_desired'         => 'no',
		'product_widget_ver'              => 'v5',
		'cart_widget_ver'                 => 'v5',
		'product_slider'                  => 'yes',
		'cart_slider'                     => 'yes',
		'widget_country'                  => 'auto',
	);

	/**
	 * Upgrade settings when new version is installed
	 *
	 * @return void
	 */
	public static function upgrade(): void {
		if ( version_compare( get_option( self::VERSION_KEY ), WC_Aplazame::VERSION, '<' ) ) {
			self::remove_redirect_page();
			global $aplazame;
			if ( ! isset( $aplazame->settings['button_image'] ) || 'https://aplazame.com/static/img/buttons/white-148x46.png' === $aplazame->settings['button_image'] ) {
				$aplazame->settings['button_image'] = self::$default_settings['button_image'];
			}
			if ( isset( $aplazame->settings['product_widget_enabled'] ) && 'no' === $aplazame->settings['product_widget_enabled'] ) {
				$aplazame->settings['product_widget_action'] = 'disabled';
			}
			if ( isset( $aplazame->settings['cart_widget_enabled'] ) && 'no' === $aplazame->settings['cart_widget_enabled'] ) {
				$aplazame->settings['cart_widget_action'] = 'disabled';
			}
			if ( ! isset( $aplazame->settings['product_legal_advice'] ) ) {
				$aplazame->settings['product_legal_advice'] = 'no';
			}
			if ( ! isset( $aplazame->settings['cart_legal_advice'] ) ) {
				$aplazame->settings['cart_legal_advice'] = 'no';
			}
			if ( isset( $aplazame->settings['title_instalments'] ) ) {
				$aplazame->settings['title'] = $aplazame->settings['title_instalments'];
			}
			self::save_settings( $aplazame->settings );

			self::update_aplazame_version();
		}
	}

	/**
	 * Uninstall
	 *
	 * @return void
	 */
	public static function uninstall(): void {
		self::remove_settings();
		self::remove_aplazame_version();
	}

	/**
	 * Update setting when save
	 *
	 * @param mixed $settings .
	 *
	 * @return void
	 */
	private static function save_settings( mixed $settings ): void {
		update_option( self::SETTINGS_KEY, $settings );
	}

	/**
	 * Default settings when reset
	 *
	 * @return array
	 */
	public static function reset_settings(): array {
		add_option( self::SETTINGS_KEY, self::$default_settings );

		return self::$default_settings;
	}

	/**
	 * Delete settings
	 *
	 * @return void
	 */
	public static function remove_settings(): void {
		delete_option( self::SETTINGS_KEY );
	}

	/**
	 * Update version number
	 *
	 * @return void
	 */
	private static function update_aplazame_version(): void {
		delete_option( self::VERSION_KEY );
		add_option( self::VERSION_KEY, WC_Aplazame::VERSION );
	}

	/**
	 * Remove old version
	 *
	 * @return void
	 */
	private static function remove_aplazame_version(): void {
		delete_option( self::VERSION_KEY );
	}

	/**
	 * Delete redirect
	 *
	 * @return void
	 */
	private static function remove_redirect_page(): void {
		include_once 'classes/lib/class-aplazame-redirect.php';
		$redirect = new Aplazame_Redirect();
		$redirect->remove_redirect_page();
	}
}

$GLOBALS['aplazame'] = new WC_Aplazame( defined( 'APLAZAME_API_BASE_URI' ) ? APLAZAME_API_BASE_URI : 'https://api.aplazame.com' );
require_once 'classes/class-wc-aplazame-proxy.php';
