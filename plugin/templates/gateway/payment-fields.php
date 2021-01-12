<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * @var WC_Aplazame $aplazame
 */
global $aplazame;
if ( ! $aplazame->enabled ) {
	return;
}

/**
 *
 * @var WooCommerce $woocommerce
 */
global $woocommerce;
?>

<script>
	(window.aplazame = window.aplazame || []).push(function (aplazame) {
		aplazame.button(
		<?php
		echo json_encode(
			array(
				'selector' => $aplazame->settings['button'],
				'amount'   => Aplazame_Sdk_Serializer_Decimal::fromFloat( $woocommerce->cart->total )->jsonSerialize(),
				'currency' => get_woocommerce_currency(),
			)
		)
		?>
		)
	})
</script>

<p><?php echo $aplazame->settings['description']; ?></p>
