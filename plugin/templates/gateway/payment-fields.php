<?php
/**
 * Payment fields
 *
 * @package WC_Aplazame
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Global aplazame var */
global $aplazame;

if ( ! $aplazame->enabled ) {
	return;
}

/**
 * Global WC var
 *
 * @var WooCommerce $woocommerce
 */
global $woocommerce;
?>

<script>
	(window.aplazame = window.aplazame || []).push(function (aplazame) {
		aplazame.button(
		<?php
		echo wp_json_encode(
			array(
				'selector' => $aplazame->settings['button'],
				'amount'   => Aplazame_Sdk_Serializer_Decimal::fromFloat( $woocommerce->cart->total )->json_serialize(),
				'currency' => get_woocommerce_currency(),
			)
		)
		?>
		)
	})
</script>

<p><?php echo esc_textarea( $aplazame->settings['description'] ); ?></p>
