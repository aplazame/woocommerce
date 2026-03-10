<?php
/**
 * Redirect
 *
 * @package WC_Aplazame/Classes/Lib
 */

/** Redirect class */
class Aplazame_Redirect {
	/**
	 * ID
	 *
	 * @var string
	 */
	public string $id;

	/**
	 * Construct
	 */
	public function __construct() {
		$this->id = $this->get_redirect_page_id();
	}

	/**
	 * Remove redirect
	 *
	 * @return void
	 */
	public function remove_redirect_page(): void {
		$id = $this->get_redirect_page_id();
		while ( $id ) {
			wp_delete_post( $id, true );
		}
	}

	/**
	 * Get redirect
	 *
	 * @return int|false
	 */
	private function get_redirect_page_id(): bool|int {
		$posts = get_posts(
			array(
				'post_type' => 'page',
				'meta_key'  => 'aplazame-redirect', //phpcs:ignore
			)
		);

		switch ( count( $posts ) ) {
			case 0:
				return false;
			default:
				return $posts[0]->ID;
		}
	}
}
