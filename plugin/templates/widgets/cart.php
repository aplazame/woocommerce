<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * @var WC_Aplazame $aplazame
 */
global $aplazame;

$country = $aplazame->settings['widget_country'] === 'auto' ? substr( get_bloginfo( 'language' ), 0, 2 ) : $aplazame->settings['widget_country'];
?>

<div
	<?php if ( $aplazame->settings['cart_widget_ver'] === 'v3' ) : ?>
		data-aplazame-widget-instalments=""
		data-view="cart"
	<?php elseif ( $aplazame->settings['cart_widget_ver'] === 'v4' ) : ?>
		data-aplazame-widget-instalments="v4"
		data-type="cart"
		data-option-max-amount-desired="<?php echo esc_attr( $aplazame->settings['cart_widget_max_desired'] === 'yes' ? 'true' : 'false' ); ?>"
		data-option-primary-color="<?php echo esc_attr( $aplazame->settings['cart_widget_primary_color'] ); ?>"
		data-option-layout="<?php echo esc_attr( $aplazame->settings['cart_widget_layout'] ); ?>"
		data-option-align="<?php echo esc_attr( $aplazame->settings['cart_widget_align'] ); ?>"
	<?php else : ?>
		data-aplazame-widget-instalments="v5"
		data-type="cart"
		data-option-slider="<?php echo esc_attr( $aplazame->settings['cart_slider'] === 'yes' ? 'true' : 'false' ); ?>"
		data-option-align="<?php echo esc_attr( $aplazame->settings['cart_widget_align'] ); ?>"
	<?php endif; ?>
	data-amount="<?php echo esc_attr( Aplazame_Sdk_Serializer_Decimal::fromFloat( WC()->cart->total )->jsonSerialize() ); ?>"
	data-country="<?php echo esc_attr( $country ); ?>"
	data-currency="<?php echo esc_attr( get_woocommerce_currency() ); ?>"
	<?php if ( ! empty( $aplazame->settings['cart_default_instalments'] ) ) : ?>
		data-option-default-instalments="<?php echo esc_attr( $aplazame->settings['cart_default_instalments'] ); ?>"
	<?php endif; ?>
	data-option-legal-advice="<?php echo esc_attr( $aplazame->settings['cart_legal_advice'] === 'yes' ? 'true' : 'false' ); ?>"
	data-option-downpayment-info="<?php echo esc_attr( $aplazame->settings['cart_downpayment_info'] === 'yes' ? 'true' : 'false' ); ?>"
	data-option-out-of-limits="<?php echo esc_attr( $aplazame->settings['widget_out_of_limits'] ); ?>"
	<?php if ( $aplazame->settings['cart_pay_in_4'] === 'yes' ) : ?>
		data-pay-in-4=""
	<?php endif; ?>
>
	<div data-aplazame-loading></div>
</div>
