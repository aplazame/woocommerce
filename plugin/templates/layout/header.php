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

$aplazameJsUri = defined( 'APLAZAME_JS_URI' ) ? APLAZAME_JS_URI : 'https://cdn.aplazame.com/aplazame.js';

$aplazameJsParams = http_build_query(
	array(
		'public_key' => $aplazame->settings['public_api_key'],
		'sandbox'    => $aplazame->sandbox ? 'true' : 'false',
	)
);
?>

<script
	type="text/javascript"
	src="<?php echo esc_attr( $aplazameJsUri ); ?>?<?php echo esc_attr( $aplazameJsParams ); ?>"
	data-api-host="<?php echo $aplazame->apiBaseUri; ?>"
	async defer
></script>
