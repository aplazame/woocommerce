<?php

/** Model Article class */
class Aplazame_Aplazame_BusinessModel_Article {
	/**
	 * Create from order item
	 *
	 * @param mixed $item .
	 *
	 * @return self
	 */
	public static function create_from_order_item( mixed $item ): Aplazame_Aplazame_BusinessModel_Article {
		if ( $item instanceof WC_Order_Item_Product ) {
			return self::create_from_order_item_product( $item );
		}

		return self::create_from_order_item_array( $item );
	}

	/**
	 * Create from order item array
	 *
	 * @param array $values .
	 *
	 * @return self
	 */
	public static function create_from_order_item_array( array $values ): Aplazame_Aplazame_BusinessModel_Article {
		$product_id = $values['product_id'];
		$product    = new WC_Product( $product_id );
		$quantity   = (int) $values['qty'];
		$price      = $values['line_subtotal'] / $values['qty'];
		$tax_rate   = $values['line_tax'] ? 100 * ( $values['line_tax'] / $values['line_total'] ) : 0;

		return self::create_article( $product, $product_id, $quantity, $price, $tax_rate );
	}

	/**
	 * Create from order item product
	 *
	 * @param WC_Order_Item_Product $item_product .
	 *
	 * @return self
	 */
	public static function create_from_order_item_product( WC_Order_Item_Product $item_product ): Aplazame_Aplazame_BusinessModel_Article {
		$product    = $item_product->get_product();
		$product_id = $product->get_id();
		$quantity   = $item_product->get_quantity();
		$price      = $item_product->get_subtotal() / $item_product->get_quantity();
		$tax_rate   = $item_product->get_total_tax() ? 100 * ( $item_product->get_total_tax() / $item_product->get_total() ) : 0;

		return self::create_article( $product, $product_id, $quantity, $price, $tax_rate );
	}

	/**
	 * Create article
	 *
	 * @param mixed $product .
	 * @param mixed $product_id .
	 * @param int   $quantity .
	 * @param mixed $price .
	 * @param mixed $tax_rate .
	 *
	 * @return self
	 */
	private static function create_article( mixed $product, mixed $product_id, int $quantity, mixed $price, mixed $tax_rate ): Aplazame_Aplazame_BusinessModel_Article {
		$a_article           = new self();
		$a_article->id       = $product_id;
		$a_article->sku      = $product->get_sku();
		$a_article->name     = $product->get_title();
		$a_article->url      = $product->get_permalink();
		$a_article->quantity = $quantity;
		$a_article->price    = Aplazame_Sdk_Serializer_Decimal::fromFloat( $price );
		$a_article->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat( $tax_rate );

		if ( ! empty( $product->get_description() ) ) {
			$a_article->description = $product->get_description();
		} elseif ( ! empty( $product->get_short_description() ) ) {
			$a_article->description = $product->get_short_description();
		}

		$image_url = wp_get_attachment_image_url( $product->get_image_id(), 'full' );
		if ( ! empty( $image_url ) ) {
			$a_article->image_url = $image_url;
		}

		return $a_article;
	}
}
