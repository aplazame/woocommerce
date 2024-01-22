<?php

class Aplazame_Aplazame_Api_BusinessModel_Article {
	public static function createFromProduct( WC_Product $product ) {
		$product_id = WC_Aplazame::_m_or_a( $product, 'get_id', 'id' );

		$article = array(
			'id'   => $product_id,
			'name' => $product->get_title(),
			'url'  => $product->get_permalink(),
		);

		if ( ! empty( $product->get_description() ) ) {
			$article['description'] = $product->get_description();
		} elseif ( ! empty( $product->get_short_description() ) ) {
			$article['description'] = $product->get_short_description();
		}

		$imageUrl = wp_get_attachment_image_url( $product->get_image_id(), 'full' );
		if ( ! empty( $imageUrl ) ) {
			$article['image_url'] = $imageUrl;
		}

		return $article;
	}
}
