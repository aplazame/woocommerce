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

		/**
		 *
		 * @var WP_Post[] $wcOrders
		 */
		$billing_email = WC_Aplazame::_m_or_a( $order, 'get_billing_email', 'billing_email' );
		if ( function_exists( 'wc_get_orders' ) ) {
			$wcOrders = wc_get_orders(
				array(
					'billing_email' => $billing_email,
				)
			);
		} else {
			$wcOrders = get_posts(
				array(
					'meta_key'   => '_billing_email',
					'meta_value' => $billing_email,
					'post_type'  => 'shop_order',
				)
			);
		}

		$historyOrders = array();

		foreach ( $wcOrders as $wcOrder ) {
			$order_id        = WC_Aplazame::_m_or_a( $wcOrder, 'get_id', 'id' );
			$historyOrders[] = Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder::createFromOrder( new WC_Order( $order_id ) );
		}

		return Aplazame_Api_Router::success( Aplazame_Sdk_Serializer_JsonSerializer::serializeValue( $historyOrders ) );
	}
}
