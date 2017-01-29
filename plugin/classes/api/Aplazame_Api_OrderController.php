<?php

final class Aplazame_Api_OrderController {
	public function history( array $params, array $queryArguments ) {
		if ( ! isset( $params['order_id'] ) ) {
			return Aplazame_Api_Router::not_found();
		}

		$order = wc_get_order( $params['order_id'] );
		if ( ! $order ) {
			return Aplazame_Api_Router::not_found();
		}

		$page      = ( isset( $queryArguments['page'] ) ) ? $queryArguments['page'] : 1;
		$page_size = ( isset( $queryArguments['page_size'] ) ) ? $queryArguments['page_size'] : 10;

		/** @var WP_Post[] $wcOrders */
		$wcOrders = get_posts( array(
			'meta_key'    => '_billing_email',
			'meta_value'  => $order->billing_email,
			'post_type'   => 'shop_order',
			'numberposts' => $page_size,
			'offset'      => ($page - 1) * $page_size,
		) );

		$historyOrders = array();

		foreach ( $wcOrders as $wcOrder ) {
			$historyOrders[] = Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder::createFromOrder( new WC_Order( $wcOrder->ID ) );
		}

		return Aplazame_Api_Router::collection( $page, $page_size, $historyOrders );
	}
}
