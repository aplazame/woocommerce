<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Aplazame_Gateway extends WC_Payment_Gateway {
	public function __construct() {
		$this->id                 = WC_Aplazame::METHOD_ID;
		$this->method_title       = WC_Aplazame::METHOD_TITLE;
		$this->method_description = __( 'Instalments pay with Aplazame', 'aplazame' );
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
		$this->settings['instalments_enabled'] = $this->settings['enabled'];
		return 'woocommerce_aplazame_settings';
	}

	public function get_icon() {
		return apply_filters(
			'woocommerce_gateway_icon',
			Aplazame_Helpers::get_html_button_image( $this->settings['button_image'], $this->get_title() ),
			$this->id
		);
	}

	public function is_available() {
		return Aplazame_Helpers::is_gateway_available( $this );
	}

	public function payment_fields() {
		Aplazame_Helpers::render_to_template( 'gateway/payment-fields.php', array( 'type' => 'instalments' ) );
	}

	public function process_payment( $order_id ) {
		return Aplazame_Helpers::do_payment( $order_id );
	}

	public function checkout( $order_id ) {
		Aplazame_Helpers::aplazame_checkout( $order_id, 'instalments' );
	}

	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		return Aplazame_Helpers::aplazame_refund( $order_id, $amount, $this->method_title );
	}

	public function checks() {
		Aplazame_Helpers::gateway_checks( $this );
	}

	public function init_form_fields() {
		$this->form_fields  = array(
			'enabled'           => array(
				'type'    => 'checkbox',
				'title'   => __( 'Enable/Disable', 'aplazame' ),
				'label'   => __( 'Enable Aplazame instalments', 'aplazame' ),
				'default' => 'yes',
			),
			'pay_later_enabled' => array(
				'type'    => 'checkbox',
				'title'   => __( 'Enable/Disable', 'aplazame' ),
				'label'   => __( 'Enable Aplazame pay later', 'aplazame' ),
				'default' => 'no',
			),
		);
		$this->form_fields += Aplazame_Helpers::form_fields();
	}

	public function init_settings() {
		$this->settings            = get_option( 'woocommerce_aplazame_settings', null );
		$this->settings['enabled'] = $this->settings['instalments_enabled'];
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
