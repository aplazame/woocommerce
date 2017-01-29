<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Aplazame_Gateway extends WC_Payment_Gateway {
	public function __construct() {
		$this->id           = WC_Aplazame::METHOD_ID;
		$this->method_title = WC_Aplazame::METHOD_TITLE;
		$this->has_fields   = true;

		# Settings
		$this->init_form_fields();
		$this->init_settings();

		$this->title   = $this->method_title;
		$this->enabled = $this->settings['enabled'];
		$this->icon    = plugins_url( 'assets/img/icon.png', dirname( __FILE__ ) );

		$this->supports = array(
			'products',
			'refunds',
		);

		add_action( 'woocommerce_update_options_payment_gateways', array(
			$this,
			'process_admin_options',
		) );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id,
			array( $this, 'process_admin_options' ) );

		add_action( 'admin_notices', array( $this, 'checks' ) );
	}

	public function is_available() {
		if ( ( $this->enabled === 'no' ) ||
		     ( ! $this->settings['public_api_key'] ) ||
		     ( ! $this->settings['private_api_key'] )
		) {
			return false;
		}

		return true;
	}

	public function payment_fields() {
		Aplazame_Helpers::render_to_template( 'gateway/payment-fields.php' );
	}

	public function process_payment( $order_id ) {
		/** @var WC_Aplazame $aplazame */
		global $aplazame;

		$url = get_permalink( $aplazame->redirect->id );
		WC()->session->redirect_order_id = $order_id;

		return array(
			'result'   => 'success',
			'redirect' => add_query_arg( array( 'order_id' => $order_id ), $url ),
		);
	}

	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		if ( ! $amount ) {
			return false;
		}

		/** @var WC_Aplazame $aplazame */
		global $aplazame;

		$client = $aplazame->get_client();

		try {
			$client->refund( $order_id, $amount );
		} catch ( Exception $e ) {
			return new WP_Error( 'aplazame_refund_error',
				sprintf( __( '%s Error: "%s"', 'aplazame' ), $this->method_title,
					$e->getMessage() ) );
		}

		$aplazame->add_order_note( $order_id,
			sprintf( __( '%s has successfully returned %d %s of the order #%s.', 'aplazame' ),
				$this->method_title, $amount, get_woocommerce_currency(), $order_id ) );

		return true;
	}

	public function checks() {
		if ( $this->enabled === 'no' ) {
			return;
		}

		$_render_to_notice = function ( $msg ) {
			echo '<div class="error"><p>' . $msg . '</p></div>';
		};

		if ( ! $this->settings['public_api_key'] || ! $this->settings['private_api_key'] ) {
			$_render_to_notice( sprintf( __( 'Aplazame gateway requires the API keys, please ' .
			                                 '<a href="%s">sign up</a> and take your keys.', 'aplazame' ),
				'https://vendors.aplazame.com/u/signup' ) );
		}
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'                         => array(
				'type'    => 'checkbox',
				'title'   => __( 'Enable/Disable', 'aplazame' ),
				'label'   => __( 'Enable Aplazame module', 'aplazame' ),
				'default' => 'yes',
			),
			'sandbox'                         => array(
				'type'        => 'checkbox',
				'title'       => __( 'Test mode (Sandbox)' ),
				'description' => __( 'Determines if the module is on Sandbox mode', 'aplazame' ),
				'label'       => __( 'Turn on Sandbox', 'aplazame' ),
			),
			'api_details'                     => array(
				'title'       => __( 'API Credentials', 'woocommerce' ),
				'type'        => 'title',
				'description' => '',
			),
			'private_api_key'                 => array(
				'type'              => 'text',
				'title'             => __( 'Private API Key', 'aplazame' ),
				'description'       => __( 'Aplazame API Private Key', 'aplazame' ),
				'custom_attributes' => array(
					'required'     => '',
				),
			),
			'public_api_key'                  => array(
				'type'              => 'text',
				'title'             => __( 'Public API Key', 'aplazame' ),
				'description'       => __( 'Aplazame API Public Key', 'aplazame' ),
				'custom_attributes' => array(
					'required' => '',
				),
			),
			'advanced'                        => array(
				'title'       => __( 'Advanced options', 'woocommerce' ),
				'type'        => 'title',
				'description' => '',
			),
			'button'                          => array(
				'type'              => 'text',
				'title'             => __( 'Button', 'aplazame' ),
				'description'       => __( 'Aplazame Button CSS Selector', 'aplazame' ),
				'placeholder'       => WC_Aplazame_Install::$defaultSettings['button'],
				'custom_attributes' => array(
					'required' => '',
				),
			),
			'quantity_selector'               => array(
				'type'        => 'text',
				'title'       => __( 'Product quantity CSS selector', 'aplazame' ),
				'description' => __( 'CSS selector pointing to product quantity', 'aplazame' ),
				'placeholder' => '#main form.cart input[name="quantity"]',
			),
			'price_product_selector'          => array(
				'type'        => 'text',
				'title'       => __( 'Product price CSS selector', 'aplazame' ),
				'description' => __( 'CSS selector pointing to product price', 'aplazame' ),
				'placeholder' => '#main .price .amount',
			),
			'price_variable_product_selector' => array(
				'type'              => 'text',
				'title'             => __( 'Variable product price CSS selector', 'aplazame' ),
				'description'       => __( 'CSS selector pointing to variable product price', 'aplazame' ),
				'default'           => WC_Aplazame_Install::$defaultSettings['price_variable_product_selector'],
				'placeholder'       => WC_Aplazame_Install::$defaultSettings['price_variable_product_selector'],
				'custom_attributes' => array(
					'required' => '',
				),
			),
		);
	}
}
