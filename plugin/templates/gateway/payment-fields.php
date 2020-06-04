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

switch ( $type ) {
	case $aplazame::INSTALMENTS:
		$text   = $aplazame->settings['description_instalments'];
		$button = $aplazame->settings['button'];
		break;
	case $aplazame::PAY_LATER:
		$text   = $aplazame->settings['description_pay_later'];
		$button = $aplazame->settings['button_pay_later'];
		break;
}
?>

<script>
	(window.aplazame = window.aplazame || []).push(function (aplazame) {
		aplazame.button(
		<?php
		echo json_encode(
			array(
				'selector' => $button,
				'amount'   => Aplazame_Sdk_Serializer_Decimal::fromFloat( $woocommerce->cart->total )->jsonSerialize(),
				'currency' => get_woocommerce_currency(),
				'product'  => array( 'type' => $type ),
			)
		)
		?>
		)
	})
</script>

<p><?php echo $text; ?></p>
