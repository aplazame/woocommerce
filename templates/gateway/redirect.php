<?php

if (!defined('ABSPATH')) {
    exit;
}

global $woocommerce;

$cart = $woocommerce->cart;
$user = wp_get_current_user();
$order = new WC_Order($_GET['order_id']);
$serializer = new Aplazame_Serializers();
?>

<script type="text/javascript">
  aplazame.checkout(<?php echo json_encode($serializer->get_checkout(
    $order, $cart->get_checkout_url(), $user), 128) ?>);
</script>
