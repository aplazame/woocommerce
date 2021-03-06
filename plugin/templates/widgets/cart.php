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
	<?php if ( $aplazame->settings['widget_legacy'] === 'yes' ) : ?>
		data-aplazame-widget-instalments=""
		data-view="cart"
	<?php else : ?>
		data-aplazame-widget-instalments="v4"
		data-type="cart"
		data-option-primary-color="<?php echo esc_attr( $aplazame->settings['cart_widget_primary_color'] ); ?>"
		data-option-layout="<?php echo esc_attr( $aplazame->settings['cart_widget_layout'] ); ?>"
	<?php endif; ?>
	data-amount="<?php echo esc_attr( Aplazame_Sdk_Serializer_Decimal::fromFloat( WC()->cart->total )->jsonSerialize() ); ?>"
	data-currency="<?php echo esc_attr( get_woocommerce_currency() ); ?>"
	<?php if ( ! empty( $aplazame->settings['cart_default_instalments'] ) ) : ?>
		data-option-default-instalments="<?php echo esc_attr( $aplazame->settings['cart_default_instalments'] ); ?>"
	<?php endif; ?>
	data-option-legal-advice="<?php echo esc_attr( $aplazame->settings['cart_legal_advice'] === 'yes' ? 'true' : 'false' ); ?>">
</div>
