<?php
/**
 * Header
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

$aplazame_js_uri = defined( 'APLAZAME_JS_URI' ) ? APLAZAME_JS_URI : 'https://cdn.aplazame.com/aplazame.js';

$aplazame_js_params = http_build_query(
	array(
		'public_key' => $aplazame->settings['public_api_key'],
		'sandbox'    => $aplazame->sandbox ? 'true' : 'false',
	)
);
?>

<script
	type="text/javascript"
	src="<?php echo esc_attr( $aplazame_js_uri ); ?>?<?php echo esc_attr( $aplazame_js_params ); ?>"
	async defer
></script>
