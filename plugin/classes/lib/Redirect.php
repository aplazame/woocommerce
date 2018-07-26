<?php

class Aplazame_Redirect {
	/**
	 *
	 * @var string
	 */
	public $id;

	public function __construct() {
		$this->id = $this->getRedirectPageId();
	}

	public function removeRedirectPage() {
		while ( $id = $this->getRedirectPageId() ) {
			wp_delete_post( $id, true );
		}
	}

	/**
	 *
	 * @return int|false
	 */
	private function getRedirectPageId() {
		$posts = get_posts(
			array(
				'post_type' => 'page',
				'meta_key'  => 'aplazame-redirect',
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
