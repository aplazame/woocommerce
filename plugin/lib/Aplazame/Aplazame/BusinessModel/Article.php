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
		$productId = $values['product_id'];
		$product   = new WC_Product( $productId );
		$quantity  = (int) $values['qty'];
		$price     = $values['line_subtotal'] / $values['qty'];
		$tax_rate  = $values['line_tax'] ? 100 * ( $values['line_tax'] / $values['line_total'] ) : 0;

		return self::createArticle( $product, $productId, $quantity, $price, $tax_rate );
	}

	public static function createFromOrderItemProduct( WC_Order_Item_Product $item_product ) {
		$product   = $item_product->get_product();
		$productId = $product->get_id();
		$quantity  = (int) $item_product->get_quantity();
		$price     = $item_product->get_subtotal() / $item_product->get_quantity();
		$tax_rate  = $item_product->get_total_tax() ? 100 * ( $item_product->get_total_tax() / $item_product->get_total() ) : 0;

		return self::createArticle( $product, $productId, $quantity, $price, $tax_rate );
	}

	private static function createArticle( $product, $productId, $quantity, $price, $tax_rate ) {
		$aArticle           = new self();
		$aArticle->id       = $productId;
		$aArticle->sku      = $product->get_sku();
		$aArticle->name     = $product->get_title();
		$aArticle->url      = $product->get_permalink();
		$aArticle->quantity = $quantity;
		$aArticle->price    = Aplazame_Sdk_Serializer_Decimal::fromFloat( $price );
		$aArticle->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat( $tax_rate );

		if ( ! empty( $product->get_description() ) ) {
			$aArticle->description = $product->get_description();
		} elseif ( ! empty( $product->get_short_description() ) ) {
			$aArticle->description = $product->get_short_description();
		}

		$imageUrl = wp_get_attachment_image_url( $product->get_image_id(), 'full' );
		if ( ! empty( $imageUrl ) ) {
			$aArticle->image_url = $imageUrl;
		}

		return $aArticle;
	}
}
