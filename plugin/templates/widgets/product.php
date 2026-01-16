<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * @var WC_Aplazame $aplazame
 */
global $aplazame;

/**
 *
 * @var WC_Product $product
 */
global $product;

switch ( WC_Aplazame::_m_or_a( $product, 'get_type', 'product_type' ) ) {
	case 'variable':
		$price_selector = $aplazame->settings['price_variable_product_selector'];
		break;
	default:
		$price_selector = $aplazame->settings['price_product_selector'];
}

if ( function_exists( 'wc_get_price_including_tax' ) ) {
	$price = wc_get_price_including_tax( $product );
} else {
	/** @noinspection PhpDeprecationInspection */
	$price = $product->get_price_including_tax();
}

$country = $aplazame->settings['widget_country'] === 'auto' ? substr( get_bloginfo( 'language' ), 0, 2 ) : $aplazame->settings['widget_country'];
?>

<div
	<?php if ( $aplazame->settings['product_widget_ver'] === 'v3' ) : ?>
		data-aplazame-widget-instalments=""
		data-view="product"
	<?php elseif ( $aplazame->settings['product_widget_ver'] === 'v4' ) : ?>
		data-aplazame-widget-instalments="v4"
		data-type="product"
		data-option-max-amount-desired="<?php echo esc_attr( $aplazame->settings['product_widget_max_desired'] === 'yes' ? 'true' : 'false' ); ?>"
		data-option-primary-color="<?php echo esc_attr( $aplazame->settings['product_widget_primary_color'] ); ?>"
		data-option-layout="<?php echo esc_attr( $aplazame->settings['product_widget_layout'] ); ?>"
		data-option-align="<?php echo esc_attr( $aplazame->settings['product_widget_align'] ); ?>"
		data-option-border-product="<?php echo esc_attr( $aplazame->settings['product_widget_border'] === 'yes' ? 'true' : 'false' ); ?>"
	<?php else : ?>
		data-aplazame-widget-instalments="v5"
		data-type="product"
		data-option-slider="<?php echo esc_attr( $aplazame->settings['product_slider'] === 'yes' ? 'true' : 'false' ); ?>"
		data-option-align="<?php echo esc_attr( $aplazame->settings['product_widget_align'] ); ?>"
	<?php endif; ?>
	<?php if ( empty( $price_selector ) ) : ?>
		data-amount="<?php echo esc_attr( Aplazame_Sdk_Serializer_Decimal::fromFloat( $price )->jsonSerialize() ); ?>"
	<?php else : ?>
		data-price="<?php echo esc_attr( $price_selector ); ?>"
	<?php endif; ?>
	<?php if ( ! empty( $aplazame->settings['quantity_selector'] ) ) : ?>
		data-qty="<?php echo esc_attr( $aplazame->settings['quantity_selector'] ); ?>"
	<?php endif; ?>
	data-country="<?php echo esc_attr( $country ); ?>"
	data-currency="<?php echo esc_attr( get_woocommerce_currency() ); ?>"
	data-article-id="<?php echo esc_attr( $product->get_id() ); ?>"
	<?php if ( ! empty( $aplazame->settings['product_default_instalments'] ) ) : ?>
		data-option-default-instalments="<?php echo esc_attr( $aplazame->settings['product_default_instalments'] ); ?>"
	<?php endif; ?>
	data-option-legal-advice="<?php echo esc_attr( $aplazame->settings['product_legal_advice'] === 'yes' ? 'true' : 'false' ); ?>"
	data-option-downpayment-info="<?php echo esc_attr( $aplazame->settings['product_downpayment_info'] === 'yes' ? 'true' : 'false' ); ?>"
	data-option-out-of-limits="<?php echo esc_attr( $aplazame->settings['widget_out_of_limits'] ); ?>"
	<?php if ( $aplazame->settings['product_pay_in_4'] === 'yes' ) : ?>
		data-pay-in-4=""
	<?php endif; ?>
>
	<div data-aplazame-loading></div>
</div>
