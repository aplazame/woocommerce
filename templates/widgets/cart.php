<?php

if (!defined('ABSPATH')) {
    exit;
}

?>

<div
  data-aplazame-simulator=""
  data-amount="<?php echo Aplazame_Filters::decimals(WC()->cart->total); ?>"
  data-currency="<?php echo get_woocommerce_currency(); ?>">
</div>
