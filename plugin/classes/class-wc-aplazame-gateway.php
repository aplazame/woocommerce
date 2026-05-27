<?php
/**
 * Aplazame gateway
 *
 * @package WC_Aplazame/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Gateway class */
class WC_Aplazame_Gateway extends WC_Payment_Gateway {
	/**
	 * Construct
	 */
	public function __construct() {
		$this->id                 = WC_Aplazame::METHOD_ID;
		$this->method_title       = WC_Aplazame::METHOD_TITLE;
		$this->method_description = __( 'Pay with Aplazame', 'aplazame' );
		$this->has_fields         = true;

		/* Settings */
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

	/**
	 * Get icon
	 *
	 * @return mixed|string|null
	 */
	public function get_icon(): mixed {
		if ( ! empty( $this->settings['button_image'] ) ) {
			$icon = '<img src="' . $this->settings['button_image'] . '" alt="' . esc_attr( $this->get_title() ) . '" />';
		} else {
			$icon = '';
		}

		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}

	/**
	 * Is (not) available
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		if ( ( 'no' === $this->enabled ) ||
			( ! $this->settings['public_api_key'] ) ||
			( ! $this->settings['private_api_key'] )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Render payment fields
	 *
	 * @return void
	 */
	public function payment_fields(): void {
		Aplazame_Helpers::render_to_template( 'gateway/payment-fields.php' );
	}

	/**
	 * Payment process
	 *
	 * @param mixed $order_id .
	 *
	 * @return array
	 */
	public function process_payment( $order_id ): array {
		$order = new WC_Order( $order_id );

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true ),
		);
	}

	/**
	 * Checkout
	 *
	 * @param mixed $order_id .
	 *
	 * @return void
	 * @throws Exception .
	 */
	public function checkout( mixed $order_id ): void {
		/**
		 * Global WooCommerce var
		 *
		 * @var WooCommerce $woocommerce
		 */
		global $woocommerce;
		global $aplazame;

		$cart  = $woocommerce->cart;
		$order = new WC_Order( $order_id );

		if ( function_exists( 'wc_get_checkout_url' ) ) {
			$checkout_url = wc_get_checkout_url();
		} else {
			/* @noinspection PhpDeprecationInspection */
			$checkout_url = $cart->get_checkout_url();
		}
		$payload = Aplazame_Aplazame_BusinessModel_Checkout::create_from_order( $order, $checkout_url );
		$payload = Aplazame_Sdk_Serializer_JsonSerializer::serialize_value( $payload );

		$client = $aplazame->get_client();
		try {
			try {
				$aplazame_payload = $client->create_checkout( $payload, 4 );
			} catch ( Exception $e ) {
				$aplazame_payload = $client->create_checkout( $payload, 3 );
			}
		} catch ( Aplazame_Sdk_Api_AplazameExceptionInterface $e ) {
			$message = $e->getMessage();
			$a_order = $client->fetch( $payload->order->id );
			if ( $a_order ) {
				wp_safe_redirect( $payload->merchant->success_url );
				exit;
			}

			$order->update_status(
				'cancelled',
				sprintf(
					/* translators: %s: order */
					__( 'Order has been cancelled: %s', 'aplazame' ),
					$message
				)
			);

			wc_add_notice( 'Aplazame Error: ' . $message, 'error' );
			wp_safe_redirect( $checkout_url );
			exit;
		}

		Aplazame_Helpers::render_to_template(
			'gateway/checkout.php',
			array(
				'aid' => $aplazame_payload['id'],
			)
		);
	}

	/**
	 * Refund process
	 *
	 * @param mixed  $order_id .
	 * @param int    $amount .
	 * @param string $reason .
	 *
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ): WP_Error|bool {
		if ( ! $amount ) {
			return false;
		}

		global $aplazame;

		$client = $aplazame->get_client();

		try {
			$client->refund( $order_id, $amount );
		} catch ( Exception $e ) {
			return new WP_Error(
				'aplazame_refund_error',
				sprintf(
					/* translators: %1$s: refund %2$s: error detail */
					__( '%1$s Error: "%2$s"', 'aplazame' ),
					$this->method_title,
					$e->getMessage()
				)
			);
		}

		$aplazame->add_order_note(
			$order_id,
			sprintf(
				/* translators: %1$s: refund %2$d: amount %3$s: coin %4$s: order */
				__( '%1$s has successfully returned %2$d %3$s of the order #%4$s.', 'aplazame' ),
				$this->method_title,
				$amount,
				get_woocommerce_currency(),
				$order_id
			)
		);

		return true;
	}

	/**
	 * Checks
	 *
	 * @return void
	 */
	public function checks(): void {
		if ( 'no' === $this->enabled ) {
			return;
		}

		$_render_to_notice = function ( $msg ) {
			echo '<div class="error"><p>' . $msg . '</p></div>';
		};

		if ( ! $this->settings['public_api_key'] || ! $this->settings['private_api_key'] ) {
			$_render_to_notice(
				sprintf(
					/* translators: %s: order */
					__(
						'Aplazame gateway requires the API keys, please <a href="%s">sign up</a> and take your keys.',
						'aplazame'
					),
					'https://vendors.aplazame.com/u/signup'
				)
			);
		}
	}

	/** Settings form */
	public function init_form_fields(): void {
		$this->form_fields = array(

			/* Base settings */
			'enabled'                         => array(
				'type'    => 'checkbox',
				'title'   => __( 'Enable/Disable', 'aplazame' ),
				'label'   => __( 'Enable Aplazame Payment', 'aplazame' ),
				'default' => 'yes',
			),
			'sandbox'                         => array(
				'type'        => 'checkbox',
				'title'       => __( 'Test mode (Sandbox)', 'aplazame' ),
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
			'widget_out_of_limits'            => array(
				'type'        => 'select',
				'title'       => __( 'Widget if Aplazame is not available', 'aplazame' ),
				'description' => __( 'Show/hide alternative widget if Aplazame is not available', 'aplazame' ),
				'options'     => array(
					'show' => __( 'Show', 'aplazame' ),
					'hide' => __( 'Hide', 'aplazame' ),
				),
				'default'     => WC_Aplazame_Install::$default_settings['widget_out_of_limits'],
			),
			'widget_country'                  => array(
				'type'        => 'select',
				'title'       => __( 'Widget country', 'aplazame' ),
				'description' => __( 'Select widget country', 'aplazame' ),
				'options'     => array(
					'auto' => __( 'Auto', 'aplazame' ),
					'es'   => __( 'ES (Spain)', 'aplazame' ),
					'pt'   => __( 'PT (Portugal)', 'aplazame' ),
				),
				'default'     => WC_Aplazame_Install::$default_settings['widget_country'],
			),
			'payment_section'                 => array(
				'title'       => __( 'Payment method title and description', 'aplazame' ),
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
				'default'           => WC_Aplazame_Install::$default_settings['description'],
				'placeholder'       => WC_Aplazame_Install::$default_settings['description'],
				'custom_attributes' => array(
					'required' => '',
				),
			),

			/* Product widget settings */
			'product_widget_section'          => array(
				'title'       => __( 'Product widget', 'woocommerce' ),
				'type'        => 'title',
				'description' => '',
			),
			'product_widget_ver'              => array(
				'type'        => 'select',
				'title'       => __( 'Widget version', 'aplazame' ),
				'description' => __( 'Select your desired version for product widget', 'aplazame' ),
				'options'     => array(
					'v3' => __( 'v3', 'aplazame' ),
					'v4' => __( 'v4', 'aplazame' ),
					'v5' => __( 'v5', 'aplazame' ),
				),
				'default'     => 'v5',
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
			'product_slider'                  => array(
				'type'        => 'checkbox',
				'title'       => __( 'Slider', 'aplazame' ),
				'description' => __( 'Show slider in product widget (only v5)', 'aplazame' ),
				'label'       => __( 'Show slider info', 'aplazame' ),
			),
			'product_downpayment_info'        => array(
				'type'        => 'checkbox',
				'title'       => __( 'Downpayment info', 'aplazame' ),
				'description' => __( 'Show downpayment info in product widget', 'aplazame' ),
				'label'       => __( 'Show downpayment info', 'aplazame' ),
			),
			'product_legal_advice'            => array(
				'type'        => 'checkbox',
				'title'       => __( 'Legal notice', 'aplazame' ),
				'description' => __( 'Show legal notice in product widget', 'aplazame' ),
				'label'       => __( 'Show legal notice', 'aplazame' ),
			),
			'product_pay_in_4'                => array(
				'type'        => 'checkbox',
				'title'       => __( 'Pay in 4', 'aplazame' ),
				'description' => __( 'Enable product widget pay in 4 (if available)', 'aplazame' ),
				'label'       => __( 'Enable pay in 4', 'aplazame' ),
			),
			'product_widget_border'           => array(
				'type'        => 'checkbox',
				'title'       => __( 'Border', 'aplazame' ),
				'description' => __( 'Show border in product widget (only v4)', 'aplazame' ),
				'label'       => __( 'Show border', 'aplazame' ),
			),
			'product_widget_max_desired'      => array(
				'type'        => 'checkbox',
				'title'       => __( 'Enter maximum instalment', 'aplazame' ),
				'description' => __( 'Allow the user to manually enter the maximum instalment they want to pay (only v4)', 'aplazame' ),
				'label'       => __( 'Allow the user to manually enter the maximum instalment', 'aplazame' ),
			),
			'product_widget_layout'           => array(
				'type'        => 'select',
				'title'       => __( 'Layout', 'aplazame' ),
				'description' => __( 'Layout of product widget (only v4)', 'aplazame' ),
				'options'     => array(
					'horizontal' => 'Horizontal',
					'vertical'   => 'Vertical',
				),
				'default'     => WC_Aplazame_Install::$default_settings['product_widget_layout'],
			),
			'product_widget_align'            => array(
				'type'        => 'select',
				'title'       => __( 'Alignment', 'aplazame' ),
				'description' => __( 'Product widget alignment (only v4/v5)', 'aplazame' ),
				'options'     => array(
					'left'   => __( 'Left', 'aplazame' ),
					'center' => __( 'Center', 'aplazame' ),
					'right'  => __( 'Right', 'aplazame' ),
				),
				'default'     => WC_Aplazame_Install::$default_settings['product_widget_align'],
			),
			'product_widget_primary_color'    => array(
				'type'        => 'text',
				'css'         => 'width:100px;',
				'class'       => 'colorpick',
				'title'       => __( 'Primary color', 'aplazame' ),
				'description' => __( 'Primary color hexadecimal code for product widget (only v4)', 'aplazame' ),
				'default'     => WC_Aplazame_Install::$default_settings['product_widget_primary_color'],
				'placeholder' => WC_Aplazame_Install::$default_settings['product_widget_primary_color'],
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
				'default'           => WC_Aplazame_Install::$default_settings['price_variable_product_selector'],
				'placeholder'       => WC_Aplazame_Install::$default_settings['price_variable_product_selector'],
				'custom_attributes' => array(
					'required' => '',
				),
			),

			/* Cart widget settings */
			'cart_widget_section'             => array(
				'title'       => __( 'Cart widget', 'woocommerce' ),
				'type'        => 'title',
				'description' => '',
			),
			'cart_widget_ver'                 => array(
				'type'        => 'select',
				'title'       => __( 'Widget version', 'aplazame' ),
				'description' => __( 'Select your desired version for cart widget', 'aplazame' ),
				'options'     => array(
					'v3' => __( 'v3', 'aplazame' ),
					'v4' => __( 'v4', 'aplazame' ),
					'v5' => __( 'v5', 'aplazame' ),
				),
				'default'     => 'v5',
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
			'cart_slider'                     => array(
				'type'        => 'checkbox',
				'title'       => __( 'Slider', 'aplazame' ),
				'description' => __( 'Show slider in cart widget (only v5)', 'aplazame' ),
				'label'       => __( 'Show slider info', 'aplazame' ),
			),
			'cart_downpayment_info'           => array(
				'type'        => 'checkbox',
				'title'       => __( 'Downpayment info', 'aplazame' ),
				'description' => __( 'Show downpayment info in cart widget', 'aplazame' ),
				'label'       => __( 'Show downpayment info', 'aplazame' ),
			),
			'cart_legal_advice'               => array(
				'type'        => 'checkbox',
				'title'       => __( 'Legal notice', 'aplazame' ),
				'description' => __( 'Show legal notice in cart widget', 'aplazame' ),
				'label'       => __( 'Show legal notice', 'aplazame' ),
			),
			'cart_pay_in_4'                   => array(
				'type'        => 'checkbox',
				'title'       => __( 'Pay in 4', 'aplazame' ),
				'description' => __( 'Enable cart widget pay in 4 (if available)', 'aplazame' ),
				'label'       => __( 'Enable pay in 4', 'aplazame' ),
			),
			'cart_widget_max_desired'         => array(
				'type'        => 'checkbox',
				'title'       => __( 'Enter maximum instalment', 'aplazame' ),
				'description' => __( 'Allow the user to manually enter the maximum instalment they want to pay (only v4)', 'aplazame' ),
				'label'       => __( 'Allow the user to manually enter the maximum instalment', 'aplazame' ),
			),
			'cart_widget_layout'              => array(
				'type'        => 'select',
				'title'       => __( 'Layout', 'aplazame' ),
				'description' => __( 'Layout of cart widget (only v4)', 'aplazame' ),
				'options'     => array(
					'horizontal' => 'Horizontal',
					'vertical'   => 'Vertical',
				),
				'default'     => WC_Aplazame_Install::$default_settings['cart_widget_layout'],
			),
			'cart_widget_align'               => array(
				'type'        => 'select',
				'title'       => __( 'Alignment', 'aplazame' ),
				'description' => __( 'Cart widget alignment (only v4/v5)', 'aplazame' ),
				'options'     => array(
					'left'   => __( 'Left', 'aplazame' ),
					'center' => __( 'Center', 'aplazame' ),
					'right'  => __( 'Right', 'aplazame' ),
				),
				'default'     => WC_Aplazame_Install::$default_settings['cart_widget_align'],
			),
			'cart_widget_primary_color'       => array(
				'type'        => 'text',
				'css'         => 'width:100px;',
				'class'       => 'colorpick',
				'title'       => __( 'Primary color', 'aplazame' ),
				'description' => __( 'Primary color hexadecimal code for cart widget (only v4)', 'aplazame' ),
				'default'     => WC_Aplazame_Install::$default_settings['cart_widget_primary_color'],
				'placeholder' => WC_Aplazame_Install::$default_settings['cart_widget_primary_color'],
			),

			/* Button settings */
			'button_section'                  => array(
				'title'       => __( 'Button', 'aplazame' ),
				'type'        => 'title',
				'description' => '',
			),
			'button'                          => array(
				'type'              => 'text',
				'title'             => __( 'Button Selector', 'aplazame' ),
				'description'       => __( 'Aplazame Button CSS Selector', 'aplazame' ),
				'placeholder'       => WC_Aplazame_Install::$default_settings['button'],
				'custom_attributes' => array(
					'required' => '',
				),
			),
			'button_image'                    => array(
				'type'        => 'text',
				'title'       => __( 'Button Image', 'aplazame' ),
				'description' => __( 'Aplazame Button Image that you want to show', 'aplazame' ),
				'placeholder' => WC_Aplazame_Install::$default_settings['button_image'],
			),
		);
	}

	/**
	 * Validate API key
	 *
	 * @param mixed $key .
	 * @param mixed $value .
	 *
	 * @return mixed
	 * @throws Exception .
	 */
	protected function validate_private_api_key_field( mixed $key, mixed $value ): mixed {
		if ( $value !== $this->settings['private_api_key'] ) {
			try {
				$response = WC_Aplazame::configure_aplazame_profile( $this->settings['sandbox'], $value );
			} catch ( Exception $e ) {
				/* Workaround https://github.com/woocommerce/woocommerce/issues/11952 */
				WC_Admin_Settings::add_error( $e->getMessage() );

				throw $e;
			}

			$this->settings['public_api_key'] = $response['public_api_key'];
		}
		return $value;
	}
}
