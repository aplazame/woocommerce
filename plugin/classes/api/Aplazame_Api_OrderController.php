<?php

final class Aplazame_Api_OrderController {
	public function history( array $params ) {
		if ( ! isset( $params['order_id'] ) ) {
			return Aplazame_Api_Router::not_found();
		}

		$order = wc_get_order( $params['order_id'] );
		if ( ! $order ) {
			return Aplazame_Api_Router::not_found();
		}

		/** @var WP_Post[] $wcOrders */
		$wcOrders = get_posts( array(
			'meta_key'    => '_billing_email',
			'meta_value'  => $order->billing_email,
			'post_type'   => 'shop_order',
		) );

		$historyOrders = array();

		foreach ( $wcOrders as $wcOrder ) {
			$historyOrders[] = Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder::createFromOrder( new WC_Order( $wcOrder->ID ) );
		}

		return $historyOrders;
	}
}
