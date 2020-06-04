<?php

class Aplazame_Helpers {
	/**
	 *
	 * @param string $template_name
	 * @param array  $args
	 */
	public static function render_to_template( $template_name, $args = array() ) {
		$template_path = WC()->template_path() . '/aplazame/';
		$default_path  = plugin_dir_path( __FILE__ ) . '../../templates/';

		wc_get_template( $template_name, $args, $template_path, $default_path );
	}

	/**
	 *
	 * @param string $image_url
	 * @param string $title
	 *
	 * @return string
	 */
	public static function get_html_button_image( $image_url, $title ) {
		if ( ! empty( $image_url ) ) {
			return '<img src="' . $image_url . '" alt="' . esc_attr( $title ) . '" />';
		}

		return '';
	}

	/**
	 *
	 * @param WC_Aplazame_Gateway|WC_Aplazame_Pay_Later_Gateway $gateway
	 *
	 * @return bool
	 */
	public static function is_gateway_available( $gateway ) {
		if ( ( $gateway->enabled === 'no' ) ||
			 ( ! $gateway->settings['public_api_key'] ) ||
			 ( ! $gateway->settings['private_api_key'] )
		) {
			return false;
		}

		return true;
	}

	/**
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public static function do_payment( $order_id ) {
		$order = new WC_Order( $order_id );

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true ),
		);
	}

	/**
	 *
	 * @param int    $order_id
	 * @param string $type
	 */
	public static function aplazame_checkout( $order_id, $type ) {
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
		$payload = Aplazame_Aplazame_BusinessModel_Checkout::createFromOrder( $order, $checkout_url, $type );
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

		self::render_to_template(
			'gateway/checkout.php',
			array(
				'aid' => $aplazame_payload['id'],
			)
		);
	}

	/**
	 *
	 * @param int    $order_id
	 * @param int    $amount
	 * @param string $method_title
	 *
	 * @return bool|WP_Error
	 */
	public static function aplazame_refund( $order_id, $amount, $method_title ) {
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
					$method_title,
					$e->getMessage()
				)
			);
		}

		$aplazame->add_order_note(
			$order_id,
			sprintf(
				__( '%1$s has successfully returned %2$d %3$s of the order #%4$s.', 'aplazame' ),
				$method_title,
				$amount,
				get_woocommerce_currency(),
				$order_id
			)
		);

		return true;
	}

	/**
	 *
	 * @param WC_Aplazame_Gateway|WC_Aplazame_Pay_Later_Gateway $gateway
	 */
	public static function gateway_checks( $gateway ) {
		if ( $gateway->enabled === 'no' ) {
			return;
		}

		$_render_to_notice = function ( $msg ) {
			echo '<div class="error"><p>' . $msg . '</p></div>';
		};

		if ( ! $gateway->settings['public_api_key'] || ! $gateway->settings['private_api_key'] ) {
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

	public static function refresh_if_api_changes( $sandbox, $private_api_key, $origin ) {
		/**
		 *
		 * @var WC_Aplazame $aplazame
		 */
		global $aplazame;

		if ( $aplazame->private_api_key != $private_api_key ) {
			try {
				$response = $aplazame->configure_aplazame_profile( $sandbox, $private_api_key );
			} catch ( Exception $e ) {
				// Workaround https://github.com/woocommerce/woocommerce/issues/11952
				WC_Admin_Settings::add_error( $e->getMessage() );

				throw $e;
			}

			$products = array();
			foreach ( $response['products'] as $product ) {
				$products[] = $product['type'];
			}

			if ( $origin == $aplazame::INSTALMENTS && ! in_array( $origin, $products ) ) {
				header( 'Refresh:0; url=' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=aplazame_' . $aplazame::PAY_LATER ) );
			} else {
				header( 'Refresh:0;' );
			}
		}
	}

	/**
	 * @param $product_type
	 *
	 * @return bool
	 */
	public static function show_fields( $product_type ) {
		/**
		 *
		 * @var WC_Aplazame $aplazame
		 */
		global $aplazame;

		return $aplazame->is_aplazame_product_available( $product_type );
	}

	/**
	 *
	 * @return array
	 */
	public static function form_fields() {
		$is_instalments_available = self::show_fields( WC_Aplazame::INSTALMENTS );
		$is_pay_later_available   = self::show_fields( WC_Aplazame::PAY_LATER );

		$form_fields = array(
			'sandbox'         => array(
				'type'        => 'checkbox',
				'title'       => __( 'Test mode (Sandbox)' ),
				'description' => __( 'Determines if the module is on Sandbox mode', 'aplazame' ),
				'label'       => __( 'Turn on Sandbox', 'aplazame' ),
			),
			'private_api_key' => array(
				'type'              => 'text',
				'title'             => __( 'Private API Key', 'aplazame' ),
				'description'       => __( 'Aplazame API Private Key', 'aplazame' ),
				'custom_attributes' => array(
					'required' => '',
				),
			),
		);

		if ( $is_instalments_available ) {
			$form_fields += array(
				'instalments_section'     => array(
					'title'       => __( 'Flexible financing', 'aplazame' ),
					'type'        => 'title',
					'description' => '',
				),
				'title_instalments'       => array(
					'type'        => 'text',
					'title'       => __( 'Title', 'aplazame' ),
					'description' => __( 'Payment method title', 'aplazame' ),
					'placeholder' => WC_Aplazame::METHOD_TITLE . __( ' - Flexible financing', 'aplazame' ),
				),
				'description_instalments' => array(
					'type'              => 'textarea',
					'title'             => __( 'Description', 'aplazame' ),
					'description'       => __( 'Payment method description', 'aplazame' ),
					'default'           => WC_Aplazame_Install::$defaultSettings['description_instalments'],
					'placeholder'       => WC_Aplazame_Install::$defaultSettings['description_instalments'],
					'custom_attributes' => array(
						'required' => '',
					),
				),
			);
		}

		if ( $is_pay_later_available ) {
			$form_fields += array(
				'pay_later_section'     => array(
					'title'       => __( 'Pay in 15 days', 'aplazame' ),
					'type'        => 'title',
					'description' => '',
				),
				'title_pay_later'       => array(
					'type'        => 'text',
					'title'       => __( 'Title', 'aplazame' ),
					'description' => __( 'Payment method title', 'aplazame' ),
					'placeholder' => WC_Aplazame::METHOD_TITLE . __( ' - Pay in 15 days', 'aplazame' ),
				),
				'description_pay_later' => array(
					'type'              => 'textarea',
					'title'             => __( 'Description', 'aplazame' ),
					'description'       => __( 'Payment method description', 'aplazame' ),
					'default'           => WC_Aplazame_Install::$defaultSettings['description_pay_later'],
					'placeholder'       => WC_Aplazame_Install::$defaultSettings['description_pay_later'],
					'custom_attributes' => array(
						'required' => '',
					),
				),
			);
		}

		if ( $is_instalments_available ) {
			$form_fields += array(
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
						'disabled' => __( '~ Not show ~', 'aplazame' ),
						'woocommerce_before_add_to_cart_button' => __( 'Before add to cart button', 'aplazame' ),
						'woocommerce_after_add_to_cart_button' => __( 'After add to cart button', 'aplazame' ),
						'woocommerce_single_product_summary' => __( 'After summary', 'aplazame' ),
					),
					'default'     => 'woocommerce_single_product_summary',
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
				'cart_legal_advice'               => array(
					'type'        => 'checkbox',
					'title'       => __( 'Legal notice', 'aplazame' ),
					'description' => __( 'Show legal notice in cart widget', 'aplazame' ),
					'label'       => __( 'Show legal notice', 'aplazame' ),
				),
				'button_section'                  => array(
					'title'       => __( 'Buttons', 'aplazame' ),
					'type'        => 'title',
					'description' => '',
				),
				'button'                          => array(
					'type'              => 'text',
					'title'             => __( '"Flexible financing" Button', 'aplazame' ),
					'description'       => __( 'Aplazame "Flexible financing" Button CSS Selector', 'aplazame' ),
					'placeholder'       => WC_Aplazame_Install::$defaultSettings['button'],
					'custom_attributes' => array(
						'required' => '',
					),
				),
				'button_image'                    => array(
					'type'        => 'text',
					'title'       => __( '"Flexible financing" Button Image', 'aplazame' ),
					'description' => __( 'Aplazame "Flexible financing" Button Image that you want to show', 'aplazame' ),
					'placeholder' => WC_Aplazame_Install::$defaultSettings['button_image'],
				),
			);
		}

		if ( $is_pay_later_available ) {
			$form_fields += array(
				'button_pay_later'       => array(
					'type'              => 'text',
					'title'             => __( '"Pay in 15 days" Button', 'aplazame' ),
					'description'       => __( 'Aplazame "Pay in 15 days" Button CSS Selector', 'aplazame' ),
					'placeholder'       => WC_Aplazame_Install::$defaultSettings['button_pay_later'],
					'custom_attributes' => array(
						'required' => '',
					),
				),
				'button_image_pay_later' => array(
					'type'        => 'text',
					'title'       => __( '"Pay in 15 days" Button Image', 'aplazame' ),
					'description' => __( 'Aplazame "Pay in 15 days" Button Image that you want to show', 'aplazame' ),
					'placeholder' => WC_Aplazame_Install::$defaultSettings['button_image_pay_later'],
				),
			);
		}

		return $form_fields;
	}
}
