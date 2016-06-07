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

switch ($product->get_type()) {
	case 'variable':
		$price_selector = '#main [itemtype="http://schema.org/Product"] .woocommerce-variation-price .amount';
		break;
	default:
		$price_selector = '#main [itemtype="http://schema.org/Product"] [itemtype="http://schema.org/Offer"] .price .amount';
}
?>

<div
	data-aplazame-simulator=""
	data-view="product"
	data-price='<?php echo $price_selector; ?>'
	data-amount="<?php echo Aplazame_Filters::decimals( $product->get_price() ); ?>"
	data-currency="<?php echo get_woocommerce_currency(); ?>"
	data-stock="<?php echo $product->is_in_stock() ? 'true' : 'false'; ?>">
</div>
