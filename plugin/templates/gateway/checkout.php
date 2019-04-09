<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * @var WC_Aplazame $aplazame
 */
global $aplazame;
if ( ! $aplazame->enabled ) {
	return;
}
?>

<script type="text/javascript">
	(window.aplazame = window.aplazame || []).push(function (aplazame) {
		aplazame.checkout("<?php echo $aid; ?>")
	})
</script>
