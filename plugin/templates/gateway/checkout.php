<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var WC_Aplazame $aplazame */
global $aplazame;
if ( ! $aplazame->enabled ) {
	return;
}
?>

<script type="text/javascript">
	aplazame.checkout(<?php echo json_encode( $checkout ); ?>);
</script>
