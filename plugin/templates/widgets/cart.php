<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * @var WC_Aplazame $aplazame
 */
global $aplazame;

?>

<div
	data-aplazame-widget-instalments=""
	data-view="cart"
	data-amount="<?php echo esc_attr( Aplazame_Sdk_Serializer_Decimal::fromFloat( WC()->cart->total )->jsonSerialize() ); ?>"
	data-currency="<?php echo esc_attr( get_woocommerce_currency() ); ?>"
	data-option-legal-advice="<?php echo esc_attr( $aplazame->settings['cart_legal_advice'] === 'yes' ? 'true' : 'false' ); ?>">
</div>
