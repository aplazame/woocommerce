<?php

/**
 * Article.
 */
class Aplazame_Aplazame_BusinessModel_Article {

	public static function createFromOrderItem( $item ) {
		if ( $item instanceof WC_Order_Item_Product ) {
			return self::createFromOrderItemProduct( $item );
		}

		return self::createFromOrderItemArray( $item );
	}

	public static function createFromOrderItemArray( array $values ) {
		$product  = new WC_Product( $values['product_id'] );
		$quantity = (int) $values['qty'];
		$price    = $values['line_subtotal'] / $values['qty'];
		$tax_rate = $values['line_tax'] ? 100 * ( $values['line_tax'] / $values['line_total'] ) : 0;

		return self::createArticle( $product, $quantity, $price, $tax_rate );
	}

	public static function createFromOrderItemProduct( WC_Order_Item_Product $item_product ) {
		$product  = new WC_Product( $item_product->get_product_id() );
		$quantity = (int) $item_product->get_quantity();
		$price    = $item_product->get_subtotal() / $item_product->get_quantity();
		$tax_rate = $item_product->get_total_tax() ? 100 * ( $item_product->get_total_tax() / $item_product->get_total() ) : 0;

		return self::createArticle( $product, $quantity, $price, $tax_rate );
	}

	private static function createArticle( WC_Product $product, $quantity, $price, $tax_rate ) {
		$aArticle     = new self();
		$aArticle->id = $product->get_id() ? $product->get_id() : null;

		$sku = $product->get_sku();
		if ( ! empty( $sku ) ) {
			$aArticle->sku = $sku;
		}

		$aArticle->name     = $product->get_title() ? $product->get_title() : null;
		$aArticle->url      = $product->get_permalink() ? $product->get_permalink() : null;
		$aArticle->quantity = $quantity;
		$aArticle->price    = Aplazame_Sdk_Serializer_Decimal::fromFloat( $price );
		$aArticle->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat( $tax_rate );

		$description = WC_Aplazame::_m_or_m( $product, 'get_post', 'get_post_data' )->post_content;
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
