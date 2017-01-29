<?php

class Aplazame_Aplazame_Api_BusinessModel_Article {
	public static function createFromProduct( WC_Product $product ) {
		$article = array(
			'id'   => $product->id,
			'name' => $product->get_title(),
			'url'  => $product->get_permalink(),
		);

		$description = $product->get_post_data()->post_content;
		if ( ! empty( $description ) ) {
			$article['description'] = $description;
		}

		$imageUrl = wp_get_attachment_url( get_post_thumbnail_id( $product->id ) );
		if ( ! empty( $imageUrl ) ) {
			$article['image_url'] = $imageUrl;
		}

		return $article;
	}
}
