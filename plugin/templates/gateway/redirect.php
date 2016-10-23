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
$user       = wp_get_current_user();
$order      = new WC_Order( $_GET['order_id'] );
?>

<script type="text/javascript">
	aplazame.checkout(<?php echo json_encode( Aplazame_Serializers::get_checkout( $order, $cart->get_checkout_url(), $user ),
	128 ) ?>);
</script>
