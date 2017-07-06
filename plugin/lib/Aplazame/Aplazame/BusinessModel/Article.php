<?php

/**
 * Article.
 */
class Aplazame_Aplazame_BusinessModel_Article {

	public static function createFromOrderItem( $item ) {
	    if ( $item instanceof WC_Order_Item_Product ) {
	    	return self::createFromOrderItemProduct($item);
	    }

	    return self::createFromOrderItemArray($item);
	}

	public static function createFromOrderItemArray( array $values ) {
		$productId = $values['product_id'];
		$product   = new WC_Product( $values['product_id'] );

		$tax_rate = 100 * ( $values['line_tax'] / $values['line_total'] );

		$aArticle           = new self();
		$aArticle->id       = $productId;
		$aArticle->sku      = $product->get_sku();
		$aArticle->name     = $product->get_title();
		$aArticle->url      = $product->get_permalink();
		$aArticle->quantity = (int) $values['qty'];
		$aArticle->price    = Aplazame_Sdk_Serializer_Decimal::fromFloat( $values['line_total'] / $values['qty'] );
		$aArticle->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat( $tax_rate );

	    $description = $product->get_post_data()->post_content;
	    if ( ! empty( $description ) ) {
		    $aArticle->description = $description;
	    }

	    $imageUrl = wp_get_attachment_url( get_post_thumbnail_id( $productId ) );
		if ( ! empty( $imageUrl ) ) {
	        $aArticle->image_url = $imageUrl;
		}

		return $aArticle;
	}

	public static function createFromOrderItemProduct( WC_Order_Item_Product $item_product ) {
	    $product = $item_product->get_product();
	    $tax_rate = 100 * ( $item_product->get_total_tax() / $item_product->get_total() );

		$aArticle = new self();
		$aArticle->id = $product->get_id();
		$aArticle->sku = $product->get_sku();
		$aArticle->name = $product->get_title();
		$aArticle->url = $product->get_permalink();
		$aArticle->quantity = (int) $item_product->get_quantity();
		$aArticle->price = Aplazame_Sdk_Serializer_Decimal::fromFloat( $item_product->get_total() / $item_product->get_quantity() );
		$aArticle->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat( $tax_rate );

	    $description = $product->get_post_data()->post_content;
	    if ( ! empty( $description ) ) {
		    $aArticle->description = $description;
	    }

	    $imageUrl = wp_get_attachment_url( get_post_thumbnail_id( $product->get_id() ) );
		if ( ! empty( $imageUrl ) ) {
	        $aArticle->image_url = $imageUrl;
		}

		return $aArticle;
	}
}
