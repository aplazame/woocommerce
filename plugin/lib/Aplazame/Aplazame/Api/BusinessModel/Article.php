<?php

/** API Model Article class */
class Aplazame_Aplazame_Api_BusinessModel_Article {
	/**
	 * Create from product
	 *
	 * @param WC_Product $product .
	 *
	 * @return array
	 */
	public static function create_from_product( WC_Product $product ): array {
		$product_id = WC_Aplazame::method_or_attribute( $product, 'get_id', 'id' );

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

		$image_url = wp_get_attachment_image_url( $product->get_image_id(), 'full' );
		if ( ! empty( $image_url ) ) {
			$article['image_url'] = $image_url;
		}

		return $article;
	}
}
