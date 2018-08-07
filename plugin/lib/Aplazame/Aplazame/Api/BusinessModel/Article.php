<?php

class Aplazame_Aplazame_Api_BusinessModel_Article {
	public static function createFromProduct( WC_Product $product ) {
		$product_id = WC_Aplazame::_m_or_a( $product, 'get_id', 'id' );

		$article = array(
			'id'   => $product_id,
			'name' => $product->get_title(),
			'url'  => $product->get_permalink(),
		);

		$description = WC_Aplazame::_m_or_m( $product, 'get_post', 'get_post_data' )->post_content;
		if ( ! empty( $description ) ) {
			$article['description'] = $description;
		}

		$imageUrl = wp_get_attachment_url( get_post_thumbnail_id( $product_id ) );
		if ( ! empty( $imageUrl ) ) {
			$article['image_url'] = $imageUrl;
		}

		return $article;
	}
}
