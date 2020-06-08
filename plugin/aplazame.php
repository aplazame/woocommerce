<?php
/*
 * Plugin Name: Aplazame
 * Plugin URI: https://github.com/aplazame/woocommerce
 * Version: 2.2.1
 * Description: Aplazame offers a payment method to receive funding for the purchases.
 * Author: Aplazame
 * Author URI: https://aplazame.com
 *
 * Text Domain: aplazame
 * Domain Path: /i18n/languages/
 *
 * WC requires at least: 2.3
 * WC tested up to: 4.2.0
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'lib/Aplazame/Sdk/autoload.php';
require_once 'lib/Aplazame/Aplazame/autoload.php';

class WC_Aplazame {
	const VERSION      = '2.2.1';
	const METHOD_ID    = 'aplazame';
	const METHOD_TITLE = 'Aplazame';

	// Product types
	const INSTALMENTS = 'instalments';
	const PAY_LATER   = 'pay_later';

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
	 *
	 * @param string $msg
	 */
	public static function log( $msg ) {
		$log = new WC_Logger();
		$log->add( self::METHOD_ID, $msg );
	}

	public static function configure_aplazame_profile( $sandbox, $private_key ) {
		/**
		 *
		 * @var WC_Aplazame $aplazame
		 */
		global $aplazame;

		$client = new Aplazame_Sdk_Api_Client(
			$aplazame->apiBaseUri,
			( $sandbox ? Aplazame_Sdk_Api_Client::ENVIRONMENT_SANDBOX : Aplazame_Sdk_Api_Client::ENVIRONMENT_PRODUCTION ),
			$private_key
		);

		return $client->get( '/me' );
	}

	/**
	 *
	 * @var array
	 */
	public $settings;
	/**
	 *
	 * @var null|bool Null when the plugin is not configured yet.
	 */
	public $enabled;
	/**
	 *
	 * @var bool
	 */
	public $sandbox;

	/**
	 *
	 * @var string
	 */
	public $apiBaseUri;

	/**
	 *
	 * @param string $apiBaseUri
	 */
	public function __construct( $apiBaseUri ) {

		// Dependencies
		include_once 'classes/lib/Helpers.php';

		register_uninstall_hook( __FILE__, 'WC_Aplazame_Install::uninstall' );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );

		// i18n
		load_plugin_textdomain( 'aplazame', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/languages' );

		// Settings
		register_activation_hook( __FILE__, 'WC_Aplazame_Install::reset_settings' );
		$this->settings = get_option( 'woocommerce_aplazame_settings' );
		if ( ! $this->settings ) {
			$this->settings = WC_Aplazame_Install::reset_settings();
		} else {
			$this->settings = array_merge( WC_Aplazame_Install::$defaultSettings, $this->settings );
		}
		$this->enabled         = $this->settings['instalments_enabled'] === 'yes' || $this->settings['pay_later_enabled'] === 'yes';
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
	}

	public function aplazame_campaigns_tab( $tabs ) {
		$tabs['aplazame_campaigns'] = array(
			'label'  => __( 'Aplazame Campaigns', 'aplazame' ),
			'target' => 'aplazame_campaigns_tab',
		);

		return $tabs;
	}

	public function product_campaigns() {
		Aplazame_Helpers::render_to_template( 'product/campaigns.php' );
	}

	public function capture_order( $order_id ) {
		if ( $this->is_aplazame_pay_later_order( $order_id ) ) {
			/**
			 *
			 * @var WC_Aplazame $aplazame
			 */
			global $aplazame;

			$client = $aplazame->get_client()->apiClient;

			$order  = wc_get_order( $order_id );
			$amount = Aplazame_Sdk_Serializer_Decimal::fromFloat( $order->get_total() - $order->get_total_refunded() )->jsonSerialize();

			try {
				$response = $client->post(
					'/orders/' . $order_id . '/captures',
					array(
						'amount' => $amount,
					)
				);
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
	 * @param array $links
	 *
	 * @return array
	 * @throws Exception
	 */
	public function plugin_action_links( $links ) {
		$extra_param = '';

		if ( self::is_aplazame_product_available( self::PAY_LATER ) ) {
			$extra_param = '_' . self::PAY_LATER;
		}

		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=aplazame' . $extra_param ) . '">' . __( 'Settings', 'aplazame' ) . '</a>',
		);
		return array_merge( $plugin_links, $links );
	}

	/**
	 *
	 * @return Aplazame_Client
	 */
	public function get_client() {
		include_once 'classes/sdk/Client.php';

		return new Aplazame_Client( $this->apiBaseUri, $this->sandbox, $this->private_api_key );
	}

	/**
	 *
	 * @param int|object|WC_Order $order_id .
	 * @param string              $msg
	 */
	public function add_order_note( $order_id, $msg ) {
		$order = new WC_Order( $order_id );
		$order->add_order_note( $msg );
	}

	// Hooks
	/**
	 *
	 * @param array $methods
	 *
	 * @return array|void
	 * @throws Exception
	 */
	public function add_gateway( $methods ) {
		if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
			return;
		}

		if ( self::is_aplazame_product_available( self::PAY_LATER ) ) {
			include_once 'classes/wc-aplazame-pay-later-gateway.php';
			$methods[] = 'WC_Aplazame_Pay_Later_Gateway';

			if ( ! self::is_aplazame_product_available( self::INSTALMENTS ) ) {
				return $methods;
			}
		}

		include_once 'classes/wc-aplazame-gateway.php';
		$methods[] = 'WC_Aplazame_Gateway';

		return $methods;
	}

	public function aplazameJs() {

		Aplazame_Helpers::render_to_template( 'layout/header.php' );
	}

	// Widgets
	public function is_product_widget_enabled() {
		return $this->settings['instalments_enabled'] === 'yes' && $this->settings['product_widget_action'] != 'disabled';
	}

	public function product_widget() {
		if ( ! $this->is_product_widget_enabled() ) {
			return;
		}

		Aplazame_Helpers::render_to_template( 'widgets/product.php' );
	}

	public function is_cart_widget_enabled() {
		return $this->settings['instalments_enabled'] === 'yes' && $this->settings['cart_widget_action'] != 'disabled';
	}

	public function cart_widget() {
		if ( ! $this->is_cart_widget_enabled() ) {
			return;
		}

		Aplazame_Helpers::render_to_template( 'widgets/cart.php' );
	}

	// Static
	/**
	 *
	 * @param int $order_id
	 *
	 * @return bool
	 */
	protected static function is_aplazame_order( $order_id ) {
		$order = wc_get_order( $order_id );
		return self::_m_or_a( $order, 'get_payment_method', 'payment_method' ) === self::METHOD_ID;
	}

	/**
	 *
	 * @param int $order_id
	 *
	 * @return bool
	 */
	protected static function is_aplazame_pay_later_order( $order_id ) {
		$order = wc_get_order( $order_id );
		return self::_m_or_a( $order, 'get_payment_method', 'payment_method' ) === self::METHOD_ID . '_' . self::PAY_LATER;
	}

	/**
	 * @param $product_type
	 *
	 * @return bool
	 */
	public function is_aplazame_product_available( $product_type ) {
		if ( ! $this->private_api_key ) {
			return false;
		}

		$client = $this->get_client()->apiClient;
		try {
			$response = $client->get( '/me' );
		} catch ( Exception $e ) {
			return false;
		}

		$products = array();
		foreach ( $response['products'] as $product ) {
			$products[] = $product['type'];
		}

		return in_array( $product_type, $products );
	}

	public function api_router() {
		$path           = isset( $_GET['path'] ) ? $_GET['path'] : '';
		$queryArguments = $_GET;
		$payload        = json_decode( file_get_contents( 'php://input' ), true );

		include_once 'classes/api/Aplazame_Api_Router.php';
		$api = new Aplazame_Api_Router( $this->private_api_key, $this->sandbox );

		$api->process( $path, $queryArguments, $payload ); // die
	}
}

class WC_Aplazame_Install {
	const SETTINGS_KEY = 'woocommerce_aplazame_settings';

	const VERSION_KEY = 'aplazame_version';

	public static $defaultSettings = array(
		'enabled'                         => null,
		'sandbox'                         => 'yes',
		'button'                          => '#payment ul li:has(input#payment_method_aplazame)',
		'quantity_selector'               => '',
		'price_product_selector'          => '',
		'price_variable_product_selector' => '#main [itemtype="http://schema.org/Product"] .single_variation_wrap .amount',
		'public_api_key'                  => '',
		'private_api_key'                 => '',
		'button_image'                    => 'https://aplazame.com/static/img/buttons/white-148x46.png',
		'product_widget_action'           => 'woocommerce_single_product_summary',
		'cart_widget_action'              => 'woocommerce_after_cart_totals',
		'instalments_enabled'             => null,
		'pay_later_enabled'               => 'no',
		'button_pay_later'                => '#payment ul li:has(input#payment_method_aplazame_pay_later)',
		'button_image_pay_later'          => 'https://aplazame.com/static/img/buttons/pay-later-227x46.png',
		'product_legal_advice'            => 'yes',
		'cart_legal_advice'               => 'yes',
		'title_instalments'               => '',
		'title_pay_later'                 => '',
		'description_instalments'         => 'Financia tu compra en segundos con <a href="https://aplazame.com" target="_blank">Aplazame</a>.
Puedes dividir el pago en cuotas mensuales y obtener una respuesta instantánea a tu solicitud. Sin comisiones ocultas.',
		'description_pay_later'           => 'Prueba primero y paga después con <a href="https://aplazame.com" target="_blank">Aplazame</a>.
Compra sin que el dinero salga de tu cuenta. Llévate todo lo que te guste y paga 15 días después de recibir tu compra sólo lo que te quedes.',
	);

	public static function upgrade() {
		if ( version_compare( get_option( self::VERSION_KEY ), WC_Aplazame::VERSION, '<' ) ) {
			self::set_aplazame_profile();
			self::remove_redirect_page();
			/**
			 *
			 * @var WC_Aplazame $aplazame
			 */
			global $aplazame;
			if ( ! isset( $aplazame->settings['button_image'] ) ) {
				$aplazame->settings['button_image'] = self::$defaultSettings['button_image'];
			}
			if ( isset( $aplazame->settings['product_widget_enabled'] ) && $aplazame->settings['product_widget_enabled'] == 'no' ) {
				$aplazame->settings['product_widget_action'] = 'disabled';
			}
			if ( isset( $aplazame->settings['cart_widget_enabled'] ) && $aplazame->settings['cart_widget_enabled'] == 'no' ) {
				$aplazame->settings['cart_widget_action'] = 'disabled';
			}
			if ( ! isset( $aplazame->settings['instalments_enabled'] ) ) {
				$aplazame->settings['instalments_enabled'] = $aplazame->settings['enabled'];
			}
			if ( ! isset( $aplazame->settings['pay_later_enabled'] ) ) {
				$aplazame->settings['pay_later_enabled'] = self::$defaultSettings['pay_later_enabled'];
			}
			if ( ! isset( $aplazame->settings['button_pay_later'] ) ) {
				$aplazame->settings['button_pay_later'] = self::$defaultSettings['button_pay_later'];
			}
			if ( ! isset( $aplazame->settings['button_image_pay_later'] ) ) {
				$aplazame->settings['button_image_pay_later'] = self::$defaultSettings['button_image_pay_later'];
			}
			if ( ! isset( $aplazame->settings['product_legal_advice'] ) ) {
				$aplazame->settings['product_legal_advice'] = 'no';
			}
			if ( ! isset( $aplazame->settings['cart_legal_advice'] ) ) {
				$aplazame->settings['cart_legal_advice'] = 'no';
			}
			if ( ! isset( $aplazame->settings['title_instalments'] ) ) {
				$aplazame->settings['title_instalments'] = self::$defaultSettings['title_instalments'];
			}
			if ( ! isset( $aplazame->settings['title_pay_later'] ) ) {
				$aplazame->settings['title_pay_later'] = self::$defaultSettings['title_pay_later'];
			}
			if ( ! isset( $aplazame->settings['description_instalments'] ) ) {
				$aplazame->settings['description_instalments'] = self::$defaultSettings['description_instalments'];
			}
			if ( ! isset( $aplazame->settings['description_pay_later'] ) ) {
				$aplazame->settings['description_pay_later'] = self::$defaultSettings['description_pay_later'];
			}
			self::save_settings( $aplazame->settings );

			self::update_aplazame_version();
		}
	}

	public static function uninstall() {
		self::remove_settings();
		self::remove_aplazame_version();
	}

	private static function save_settings( $settings ) {
		update_option( self::SETTINGS_KEY, $settings );
	}

	/**
	 *
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

	private static function remove_aplazame_version() {
		delete_option( self::VERSION_KEY );
	}

	private static function set_aplazame_profile() {
		/**
		 *
		 * @var WC_Aplazame $aplazame
		 */
		global $aplazame;

		if ( ! $aplazame->private_api_key ) {
			return;
		}

		$client = $aplazame->get_client()->apiClient;
		try {
			$client->patch(
				'/me',
				array(
					'confirmation_url' => '',
				)
			);
		} catch ( Exception $e ) {
			$aplazame->private_api_key             = null;
			$aplazame->settings['private_api_key'] = null;
		}
	}

	private static function remove_redirect_page() {
		include_once 'classes/lib/Redirect.php';
		$redirect = new Aplazame_Redirect();
		$redirect->removeRedirectPage();
	}
}

$GLOBALS['aplazame'] = new WC_Aplazame( defined( 'APLAZAME_API_BASE_URI' ) ? APLAZAME_API_BASE_URI : 'https://api.aplazame.com' );
require_once 'classes/wc-aplazame-proxy.php';
