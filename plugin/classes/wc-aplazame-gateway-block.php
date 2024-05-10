<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Aplazame_Gateway_Blocks_Support extends AbstractPaymentMethodType {
	private $gateway;
	protected $name = WC_Aplazame::METHOD_ID;

	public function initialize() {
		$this->settings = get_option( "woocommerce_{$this->name}_settings", [] );
		$gateways = WC()->payment_gateways->payment_gateways();
		$this->gateway  = $gateways[ $this->name ];
	}

	public function is_active() {
		return $this->gateway->is_available();
	}

	public function get_payment_method_script_handles() {
		wp_register_script(
			'wc-aplazame-blocks-integration',
			plugin_dir_url( __FILE__ ) . '../resources/payment-block.js',
			array(
				'wc-blocks-registry',
				'wc-settings',
				'wp-element',
				'wp-html-entities',
			),
			null,
			true
		);
		return [ 'wc-aplazame-blocks-integration' ];
	}

	public function get_payment_method_data() {
		return [
			'title'     => $this->gateway->title,
			'description'     => $this->gateway->settings['description'],
		];
	}
}
