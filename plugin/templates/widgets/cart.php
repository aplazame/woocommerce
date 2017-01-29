<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var WC_Aplazame $aplazame */
global $aplazame;
if ( ! $aplazame->enabled ) {
	return;
}

?>

<div
	data-aplazame-simulator=""
	data-view="cart"
	data-amount="<?php echo esc_attr( Aplazame_Sdk_Serializer_Decimal::fromFloat( WC()->cart->total )->jsonSerialize() ); ?>"
	data-currency="<?php echo esc_attr( get_woocommerce_currency() ); ?>">
</div>
