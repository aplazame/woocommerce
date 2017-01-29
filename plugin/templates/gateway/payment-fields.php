<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var WC_Aplazame $aplazame */
global $aplazame;
if ( ! $aplazame->enabled ) {
	return;
}

/** @var WooCommerce $woocommerce */
global $woocommerce;
?>

<!-- TODO: aplazame-js feature require -->
<style type="text/css">
	#payment ul li.payment_method_aplazame {
		/*display: none;*/
	}
</style>

<noscript>
	<?php printf( __( 'It is necessary to enable JavaScript, %s does not work without JS.', 'aplazame' ), 'Aplazame' ) ?>
</noscript>

<p>
	Aplaza o fracciona tu compra con <a href="https://aplazame.com" target="_blank">Aplazame</a>.<br>
	Obtén financiación al instante sólo con tu Nombre y Apellidos, DNI/NIE, Teléfono y tarjeta de débito o crédito.<br>
	Sin comisiones ocultas ni letra pequeña.<br>
</p>

<script>
	aplazame.button({
		selector: <?php echo json_encode( $aplazame->settings['button'] ); ?>,
		amount: <?php echo json_encode( Aplazame_Sdk_Serializer_Decimal::fromFloat( $woocommerce->cart->total )->jsonSerialize() ); ?>,
		currency: <?php echo json_encode( get_woocommerce_currency() ) ?>
	});
</script>
