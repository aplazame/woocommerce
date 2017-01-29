<?php

/**
 * Article.
 */
class Aplazame_Aplazame_BusinessModel_Article {

	public static function crateFromOrderItem( array $values ) {
	    $product = new WC_Product( $values['product_id'] );
	    $tax_rate = 100 * ( $values['line_tax'] / $values['line_total'] );

		$aArticle = new self();
		$aArticle->id = $product->id;
		$aArticle->sku = $product->get_sku();
		$aArticle->name = $product->get_title();
		$aArticle->url = $product->get_permalink();
		$aArticle->quantity = (int) $values['qty'];
		$aArticle->price = Aplazame_Sdk_Serializer_Decimal::fromFloat( $values['line_total'] / $values['qty'] );
		$aArticle->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat( $tax_rate );

	    $description = $product->get_post_data()->post_content;
	    if ( ! empty( $description ) ) {
		    $aArticle->description = $description;
	    }

	    $imageUrl = wp_get_attachment_url( get_post_thumbnail_id( $product->id ) );
		if ( ! empty( $imageUrl ) ) {
	        $aArticle->image_url = $imageUrl;
		}

		return $aArticle;
	}
}
