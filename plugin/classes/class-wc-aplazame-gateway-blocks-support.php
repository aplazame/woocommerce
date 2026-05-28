<?php
/**
 * Aplazame gateway blocks support
 *
 * @package WC_Aplazame/Classes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/** Block support class */
final class WC_Aplazame_Gateway_Blocks_Support extends AbstractPaymentMethodType {

	/**
	 * Gateway
	 *
	 * @var mixed
	 */
	private mixed $gateway;

	/**
	 * Name
	 *
	 * @var string
	 */
	protected $name = WC_Aplazame::METHOD_ID;

	/**
	 * Initialize
	 *
	 * @return void
	 */
	public function initialize(): void {
		$this->settings = get_option( "woocommerce_{$this->name}_settings", array() );
		$gateways       = WC()->payment_gateways->payment_gateways();
		$this->gateway  = $gateways[ $this->name ];
	}

	/**
	 * Is (not) active
	 *
	 * @return bool
	 */
	public function is_active(): bool {
		return $this->gateway->is_available();
	}

	/**
	 * Script
	 *
	 * @return string[]
	 */
	public function get_payment_method_script_handles(): array {
		$asset_path   = plugin_dir_path( __FILE__ ) . '../resources/payment-block.asset.php';
		$version      = null;
		$dependencies = array();
		if ( file_exists( $asset_path ) ) {
			$asset        = require $asset_path;
			$version      = $asset['version'] ?? $version;
			$dependencies = $asset['dependencies'] ?? $dependencies;
		}
		wp_register_script(
			'wc-aplazame-blocks-integration',
			plugin_dir_url( __FILE__ ) . '../resources/payment-block.js',
			$dependencies,
			$version,
			true
		);
		return array( 'wc-aplazame-blocks-integration' );
	}

	/**
	 * Method data
	 *
	 * @return array
	 */
	public function get_payment_method_data(): array {
		return array(
			'title'       => $this->gateway->title,
			'description' => $this->gateway->settings['description'],
		);
	}
}
