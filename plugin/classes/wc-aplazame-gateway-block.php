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
		$asset_path   = plugin_dir_path( __FILE__ ) . '../resources/payment-block.asset.php';
		$version      = null;
		$dependencies = array();
		if( file_exists( $asset_path ) ) {
			$asset        = require $asset_path;
			$version      = isset( $asset[ 'version' ] ) ? $asset[ 'version' ] : $version;
			$dependencies = isset( $asset[ 'dependencies' ] ) ? $asset[ 'dependencies' ] : $dependencies;
		}
		wp_register_script(
			'wc-aplazame-blocks-integration',
			plugin_dir_url( __FILE__ ) . '../resources/payment-block.js',
			$dependencies,
			$version,
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
