<?php

final class Aplazame_Api_Serializer {
	/**
	 * @param WC_Product $product
	 *
	 * @return array
	 */
	public static function article( WC_Product $product ) {
		$serialized = array(
			'id'   => $product->id,
			'name' => $product->get_title(),
			'url'  => $product->get_permalink(),
		);

		$description = $product->get_post_data()->post_content;
		if ( ! empty( $description ) ) {
			$serialized['description'] = $description;
		}

		$imageUrl = wp_get_attachment_url( get_post_thumbnail_id( $product->id ) );
		if ( ! empty( $imageUrl ) ) {
			$serialized['image_url'] = $imageUrl;
		}

		return $serialized;
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public static function historicalOrder( WC_Order $order ) {
		$orderDate = new DateTime( $order->order_date );

		$serialized = array(
			'id'         => (string) $order->id,
			'amount'     => Aplazame_Filters::decimals( $order->get_total() ),
			'due'        => '',
			'status'     => $order->get_status(),
			'type'       => Aplazame_Helpers::get_payment_method( $order->id ),
			'order_date' => $orderDate->format( DATE_ISO8601 ),
			'currency'   => $order->get_order_currency(),
			'billing'    => Aplazame_Serializers::get_address( $order, 'billing' ),
			'shipping'   => Aplazame_Serializers::get_shipping_info( $order ),
		);

		return $serialized;
	}
}
