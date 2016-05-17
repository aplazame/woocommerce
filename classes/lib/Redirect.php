<?php

class Aplazame_Redirect
{
	/**
	 * @return bool
	 */
	public static function is_redirect() {

		return get_the_ID() === static::get_the_ID();
	}

	/**
	 * @return int
	 */
	public static function get_the_ID() {

		$posts = get_posts(array(
			'post_type' => 'page',
			'meta_key' => 'aplazame-redirect',
			'meta_value' => 'true',
			'numberposts' => -1,
		));

		if ( empty( $posts ) ) {
			$defaults = array(
				'post_title' => __( 'Aplazame Redirect' ),
				'post_type' => 'page',
				'post_status' => 'publish',
			);

			$post_id = array( wp_insert_post( $defaults ) );
			$post_id = $post_id[0];
			add_post_meta( $post_id, 'aplazame-redirect', 'true' );
		} else {
			$post_id = $posts[0]->ID;
		}

		return $post_id;
	}

	public static function payload() {

		if ( static::is_redirect() &&
				((string) WC()->session->redirect_order_id === $_GET['order_id']) ) {
			Aplazame_Helpers::render_to_template( 'gateway/redirect.php' );
		}
	}
}
