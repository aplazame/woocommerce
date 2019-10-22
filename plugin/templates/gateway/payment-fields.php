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
	case 'instalments':
		$text   = 'Financia tu compra en segundos y sin documentación con <a href="https://aplazame.com" target="_blank">Aplazame</a>.
				Puedes dividir el pago en cuotas mensuales y obtener una respuesta instantánea a tu solicitud. Sin comisiones ocultas.';
		$button = $aplazame->settings['button'];
		break;
	case 'pay_later':
		$text   = 'Prueba primero y paga después con <a href="https://aplazame.com" target="_blank">Aplazame</a>.
 				Compra sin que el dinero salga de tu cuenta. Llévate todo lo que te guste y paga 15 días después de recibir tu compra sólo lo que te quedes.';
		$button = $aplazame->settings['button_pay_later'];
		break;
}
?>

<script>
	(window.aplazame = window.aplazame || []).push(function (aplazame) {
		aplazame.button(
		<?php
		echo json_encode(
			[
				'selector' => $button,
				'amount'   => Aplazame_Sdk_Serializer_Decimal::fromFloat( $woocommerce->cart->total )->jsonSerialize(),
				'currency' => get_woocommerce_currency(),
				'product'  => [ 'type' => $type ],
			]
		)
		?>
		)
	})
</script>

<p><?php echo $text; ?></p>
