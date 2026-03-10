<?php
/**
 * Helpers
 *
 * @package WC_Aplazame/Classes/Lib
 */

/** Helpers class */
class Aplazame_Helpers {
	/**
	 * Render to template
	 *
	 * @param string $template_name .
	 * @param array  $args .
	 */
	public static function render_to_template( string $template_name, array $args = array() ): void {
		$template_path = WC()->template_path() . '/aplazame/';
		$default_path  = plugin_dir_path( __FILE__ ) . '../../templates/';

		wc_get_template( $template_name, $args, $template_path, $default_path );
	}
}
