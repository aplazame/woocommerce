<?php

class Aplazame_Redirect {
	/**
	 * @var string
	 */
	public $id;

	public function __construct() {
		$this->id = $this->getRedirectPageId();
	}

	/**
	 * @return bool
	 */
	public function isRedirect( $id ) {

		return ( $this->id === $id );
	}

	public function checkout() {
		global $wp_query;
	    $post_id = $wp_query->get_queried_object_id();

		if ( ! isset( $_GET['order_id'] )
		     || ( (string) WC()->session->redirect_order_id !== $_GET['order_id'] )
		     || ! $this->isRedirect( $post_id )
		) {
			return;
		}

		Aplazame_Helpers::render_to_template( 'gateway/redirect.php' );
	}

	/**
	 * @return int|WP_Error
	 */
	public function addRedirectPage() {
		if ( $this->id ) {
			return $this->id;
		}

		$post = array(
			'post_name'  => 'aplazame-redirect',
			'post_title'  => __( 'Aplazame Redirect' ),
			'post_type'   => 'page',
			'post_status' => 'publish',
		);

		$id = wp_insert_post( $post );

		// Compatibility with WP < 4.4
		add_post_meta( $id, 'aplazame-redirect', 'true' );

		return $id;
	}

	public function removeRedirectPage() {
		while ( $id = $this->getRedirectPageId() ) {
			wp_delete_post( $id, true );
		}
	}

	/**
	 * @return int|false
	 */
	private function getRedirectPageId() {
		$posts = get_posts( array(
			'post_type' => 'page',
			'meta_key'  => 'aplazame-redirect',
		) );

		switch ( count( $posts ) ) {
			case 0:
				return false;
			default:
				return $posts[0]->ID;
		}
	}
}
