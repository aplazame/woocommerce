<?php
/**
 * Checkout init
 *
 * @package WC_Aplazame
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Global aplazame var */
global $aplazame;

if ( ! $aplazame->enabled ) {
	return;
}
?>

<script type="text/javascript">
	(window.aplazame = window.aplazame || []).push(function (aplazame) {
		aplazame.checkout("<?php echo esc_attr( $aid ); ?>")
	})
</script>
