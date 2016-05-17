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

<!-- TODO: nav hook and exclude redirect page -->
<style type="text/css">
	li.page-item-<?php echo Aplazame_Redirect::get_the_ID() ?> {
	display: none;
	}
</style>

<script
	type="text/javascript"
	src="<?php echo $aplazame->host . '/static/aplazame.js'; ?>"
	data-aplazame="publicKey: <?php echo $aplazame->settings['public_api_key']; ?>"
	data-version="<?php echo $aplazame->settings['api_version']; ?>"
	data-sandbox="<?php echo $aplazame->sandbox?'true':'false'; ?>"
	data-analytics="<?php echo ($aplazame->settings['enable_analytics'] === 'yes')?'true':'false'; ?>">
</script>
