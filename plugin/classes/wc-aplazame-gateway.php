<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Aplazame_Gateway extends WC_Payment_Gateway {
	public function __construct() {
		$this->id                 = WC_Aplazame::METHOD_ID;
		$this->method_title       = WC_Aplazame::METHOD_TITLE;
		$this->method_description = __( 'Pay with Aplazame', 'aplazame' );
		$this->has_fields         = true;

		// Settings
		$this->init_form_fields();
		$this->init_settings();

		$this->title   = $this->settings['title'] ? $this->settings['title'] : $this->method_title;
		$this->enabled = $this->settings['enabled'];

		$this->supports = array(
			'products',
			'refunds',
		);

		add_action(
			'woocommerce_update_options_payment_gateways',
			array(
				$this,
				'process_admin_options',
			)
		);

		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array( $this, 'process_admin_options' )
		);

		add_action( 'admin_notices', array( $this, 'checks' ) );

		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'checkout' ) );
	}

	public function get_icon() {
		if ( ! empty( $this->settings['button_image'] ) ) {
			$icon = '<img src="' . $this->settings['button_image'] . '" alt="' . esc_attr( $this->get_title() ) . '" />';
		} else {
			$icon = '';
		}

		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
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
		$order = new WC_Order( $order_id );

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true ),
		);
	}

	public function checkout( $order_id ) {
		/**
		 *
		 * @var WooCommerce $woocommerce
		 */
		global $woocommerce;
		/**
		 *
		 * @var WC_Aplazame $aplazame
		 */
		global $aplazame;

		$cart  = $woocommerce->cart;
		$order = new WC_Order( $order_id );

		if ( function_exists( 'wc_get_checkout_url' ) ) {
			$checkout_url = wc_get_checkout_url();
		} else {
			/** @noinspection PhpDeprecationInspection */
			$checkout_url = $cart->get_checkout_url();
		}
		$payload = Aplazame_Aplazame_BusinessModel_Checkout::createFromOrder( $order, $checkout_url );
		$payload = Aplazame_Sdk_Serializer_JsonSerializer::serializeValue( $payload );

		$client = $aplazame->get_client();
		try {
			$aplazame_payload = $client->create_checkout( $payload );
		} catch ( Aplazame_Sdk_Api_AplazameExceptionInterface $e ) {
			$message = $e->getMessage();
			$aOrder  = $client->fetch( $payload->order->id );
			if ( $aOrder ) {
				wp_redirect( $payload->merchant->success_url );
				exit;
			}

			$order->update_status(
				'cancelled',
				sprintf(
					__( 'Order has been cancelled: %s', 'aplazame' ),
					$message
				)
			);

			wc_add_notice( 'Aplazame Error: ' . $message, 'error' );
			wp_redirect( $checkout_url );
			exit;
		}

		Aplazame_Helpers::render_to_template(
			'gateway/checkout.php',
			array(
				'aid' => $aplazame_payload['id'],
			)
		);
	}

	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		if ( ! $amount ) {
			return false;
		}

		/**
		 *
		 * @var WC_Aplazame $aplazame
		 */
		global $aplazame;

		$client = $aplazame->get_client();

		try {
			$client->refund( $order_id, $amount );
		} catch ( Exception $e ) {
			return new WP_Error(
				'aplazame_refund_error',
				sprintf(
					__( '%1$s Error: "%2$s"', 'aplazame' ),
					$this->method_title,
					$e->getMessage()
				)
			);
		}

		$aplazame->add_order_note(
			$order_id,
			sprintf(
				__( '%1$s has successfully returned %2$d %3$s of the order #%4$s.', 'aplazame' ),
				$this->method_title,
				$amount,
				get_woocommerce_currency(),
				$order_id
			)
		);

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
			$_render_to_notice(
				sprintf(
					__(
						'Aplazame gateway requires the API keys, please <a href="%s">sign up</a> and take your keys.',
						'aplazame'
					),
					'https://vendors.aplazame.com/u/signup'
				)
			);
		}
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'                         => array(
				'type'    => 'checkbox',
				'title'   => __( 'Enable/Disable', 'aplazame' ),
				'label'   => __( 'Enable Aplazame Payment', 'aplazame' ),
				'default' => 'yes',
			),
			'sandbox'                         => array(
				'type'        => 'checkbox',
				'title'       => __( 'Test mode (Sandbox)' ),
				'description' => __( 'Determines if the module is on Sandbox mode', 'aplazame' ),
				'label'       => __( 'Turn on Sandbox', 'aplazame' ),
			),
			'private_api_key'                 => array(
				'type'              => 'text',
				'title'             => __( 'Private API Key', 'aplazame' ),
				'description'       => __( 'Aplazame API Private Key', 'aplazame' ),
				'custom_attributes' => array(
					'required' => '',
				),
			),
			'instalments_section'             => array(
				'title'       => __( 'Flexible financing', 'aplazame' ),
				'type'        => 'title',
				'description' => '',
			),
			'title'                           => array(
				'type'        => 'text',
				'title'       => __( 'Title', 'aplazame' ),
				'description' => __( 'Payment method title', 'aplazame' ),
				'placeholder' => WC_Aplazame::METHOD_TITLE,
			),
			'description'                     => array(
				'type'              => 'textarea',
				'title'             => __( 'Description', 'aplazame' ),
				'description'       => __( 'Payment method description', 'aplazame' ),
				'default'           => WC_Aplazame_Install::$defaultSettings['description'],
				'placeholder'       => WC_Aplazame_Install::$defaultSettings['description'],
				'custom_attributes' => array(
					'required' => '',
				),
			),
			'product_widget_section'          => array(
				'title'       => __( 'Product widget', 'woocommerce' ),
				'type'        => 'title',
				'description' => '',
			),
			'product_widget_action'           => array(
				'type'        => 'select',
				'title'       => __( 'Place to show', 'aplazame' ),
				'description' => __( 'Widget place on product page', 'aplazame' ),
				'options'     => array(
					'disabled'                             => __( '~ Not show ~', 'aplazame' ),
					'woocommerce_before_add_to_cart_button' => __( 'Before add to cart button', 'aplazame' ),
					'woocommerce_after_add_to_cart_button' => __( 'After add to cart button', 'aplazame' ),
					'woocommerce_single_product_summary'   => __( 'After summary', 'aplazame' ),
				),
				'default'     => 'woocommerce_single_product_summary',
			),
			'product_default_instalments'     => array(
				'type'        => 'text',
				'css'         => 'width:200px;',
				'title'       => __( 'Default instalments', 'aplazame' ),
				'description' => __( 'Number of default instalments in product widget', 'aplazame' ),
				'placeholder' => __( 'Optional (only numbers)', 'aplazame' ),
			),
			'product_legal_advice'            => array(
				'type'        => 'checkbox',
				'title'       => __( 'Legal notice', 'aplazame' ),
				'description' => __( 'Show legal notice in product widget', 'aplazame' ),
				'label'       => __( 'Show legal notice', 'aplazame' ),
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
			'cart_widget_section'             => array(
				'title'       => __( 'Cart widget', 'woocommerce' ),
				'type'        => 'title',
				'description' => '',
			),
			'cart_widget_action'              => array(
				'type'        => 'select',
				'title'       => __( 'Place to show', 'aplazame' ),
				'description' => __( 'Widget place on cart page', 'aplazame' ),
				'options'     => array(
					'disabled'                       => __( '~ Not show ~', 'aplazame' ),
					'woocommerce_before_cart_totals' => __( 'Before cart totals', 'aplazame' ),
					'woocommerce_after_cart_totals'  => __( 'After cart totals', 'aplazame' ),
				),
				'default'     => 'woocommerce_after_cart_totals',
			),
			'cart_default_instalments'        => array(
				'type'        => 'text',
				'css'         => 'width:200px;',
				'title'       => __( 'Default instalments', 'aplazame' ),
				'description' => __( 'Number of default instalments in cart widget', 'aplazame' ),
				'placeholder' => __( 'Optional (only numbers)', 'aplazame' ),
			),
			'cart_legal_advice'               => array(
				'type'        => 'checkbox',
				'title'       => __( 'Legal notice', 'aplazame' ),
				'description' => __( 'Show legal notice in cart widget', 'aplazame' ),
				'label'       => __( 'Show legal notice', 'aplazame' ),
			),
			'button_section'                  => array(
				'title'       => __( 'Button', 'aplazame' ),
				'type'        => 'title',
				'description' => '',
			),
			'button'                          => array(
				'type'              => 'text',
				'title'             => __( 'Button Selector', 'aplazame' ),
				'description'       => __( 'Aplazame Button CSS Selector', 'aplazame' ),
				'placeholder'       => WC_Aplazame_Install::$defaultSettings['button'],
				'custom_attributes' => array(
					'required' => '',
				),
			),
			'button_image'                    => array(
				'type'        => 'text',
				'title'       => __( 'Button Image', 'aplazame' ),
				'description' => __( 'Aplazame Button Image that you want to show', 'aplazame' ),
				'placeholder' => WC_Aplazame_Install::$defaultSettings['button_image'],
			),
		);
	}

	protected function validate_private_api_key_field( $key, $value ) {
		try {
			$response = WC_Aplazame::configure_aplazame_profile( $this->settings['sandbox'], $value );
		} catch ( Exception $e ) {
			// Workaround https://github.com/woocommerce/woocommerce/issues/11952
			WC_Admin_Settings::add_error( $e->getMessage() );

			throw $e;
		}

		$this->settings['public_api_key'] = $response['public_api_key'];

		return $value;
	}
}
