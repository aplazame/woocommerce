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

<p>
	Paga en 15 días tu compra con <a href="https://aplazame.com" target="_blank">Aplazame</a>.<br>
	Obtén financiación al instante sólo con tu Nombre y Apellidos, DNI/NIE, Teléfono y tarjeta de débito o crédito.<br>
	Sin comisiones ocultas ni letra pequeña.<br>
</p>

<script>
	(window.aplazame = window.aplazame || []).push(function (aplazame) {
		aplazame.button({
			selector: <?php echo json_encode( $aplazame->settings['button_pay_later'] ); ?>,
			amount: <?php echo json_encode( Aplazame_Sdk_Serializer_Decimal::fromFloat( $woocommerce->cart->total )->jsonSerialize() ); ?>,
			currency: <?php echo json_encode( get_woocommerce_currency() ); ?>,
			product: {
				type: 'pay_later'
			}
		})
	})
</script>
