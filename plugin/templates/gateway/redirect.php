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

$cart       = $woocommerce->cart;
$order      = new WC_Order( $_GET['order_id'] );
$checkout   = Aplazame_Aplazame_BusinessModel_Checkout::createFromOrder( $order, $cart->get_checkout_url(), $aplazame->redirect->id );
?>

<script type="text/javascript">
	aplazame.checkout(<?php echo json_encode( Aplazame_Sdk_Serializer_JsonSerializer::serializeValue( $checkout ) ); ?>);
</script>
