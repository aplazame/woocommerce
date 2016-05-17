<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var WC_Aplazame $aplazame */
global $aplazame;
if ( ! $aplazame->enabled ) {
	return;
}

/** @var WC_Product $product */
global $product;
?>

<div
	data-aplazame-simulator=""
	data-view="product"
	data-amount="<?php echo Aplazame_Filters::decimals( $product->get_price() ); ?>"
	data-currency="<?php echo get_woocommerce_currency(); ?>"
	data-stock="<?php echo $product->is_in_stock()?'true':'false'; ?>">
</div>
