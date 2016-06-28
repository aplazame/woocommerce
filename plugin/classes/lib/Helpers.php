<?php

class Aplazame_Helpers {
	/**
	 * @param string $template_name
	 * @param array  $args
	 */
	public static function render_to_template( $template_name, $args = array() ) {
		$template_path = WC()->template_path() . '/aplazame/';
		$default_path  = plugin_dir_path( __FILE__ ) . '../../templates/';

		wc_get_template( $template_name, $args, $template_path, $default_path );
	}

	/**
	 * @param int $order_id
	 *
	 * @return string
	 */
	public static function get_payment_method( $order_id ) {
		return get_post_meta( $order_id, '_payment_method', true );
	}
}
