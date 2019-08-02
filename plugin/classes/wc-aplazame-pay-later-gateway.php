<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Aplazame_Pay_Later_Gateway extends WC_Payment_Gateway {
	public function __construct() {
		$this->id                 = WC_Aplazame::METHOD_ID . '_pay_later';
		$this->method_title       = WC_Aplazame::METHOD_TITLE . ' Pay Later';
		$this->method_description = __( 'Pay later with Aplazame', 'aplazame' );
		$this->has_fields         = true;

		// Settings
		$this->init_form_fields();
		$this->init_settings();

		$this->title   = $this->method_title;
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

	public function get_option_key() {
		$this->settings['pay_later_enabled'] = $this->settings['enabled'];
		return 'woocommerce_aplazame_settings';
	}

	public function get_icon() {
		if ( ! empty( $this->settings['button_image_pay_later'] ) ) {
			$icon = '<img src="' . $this->settings['button_image_pay_later'] . '" alt="' . esc_attr( $this->get_title() ) . '" />';
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
		Aplazame_Helpers::render_to_template( 'gateway/payment-fields-pay-later.php' );
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
		$payload = Aplazame_Aplazame_BusinessModel_Checkout::createFromOrder( $order, $checkout_url, 'pay_later' );
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
		$this->form_fields  = array(
			'instalments_enabled' => array(
				'type'    => 'checkbox',
				'title'   => __( 'Enable/Disable', 'aplazame' ),
				'label'   => __( 'Enable Aplazame instalments', 'aplazame' ),
				'default' => 'yes',
			),
			'enabled'             => array(
				'type'    => 'checkbox',
				'title'   => __( 'Enable/Disable', 'aplazame' ),
				'label'   => __( 'Enable Aplazame pay later', 'aplazame' ),
				'default' => 'no',
			),
		);
		$this->form_fields += WC_Aplazame_Gateway::form_fields();
	}

	public function init_settings() {
		$this->settings            = get_option( 'woocommerce_aplazame_settings', null );
		$this->settings['enabled'] = $this->settings['pay_later_enabled'];
		$this->enabled             = ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'] ? 'yes' : 'no';
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
