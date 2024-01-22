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
		 * @var WC_Order_Query[] $wcOrders
		 */
		$billing_email = WC_Aplazame::_m_or_a( $order, 'get_billing_email', 'billing_email' );
		$wcOrders      = wc_get_orders(
			array(
				'billing_email' => $billing_email,
			)
		);

		$historyOrders = array();

		foreach ( $wcOrders as $wcOrder ) {
			$order_id        = WC_Aplazame::_m_or_a( $wcOrder, 'get_id', 'id' );
			$historyOrders[] = Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder::createFromOrder( new WC_Order( $order_id ) );
		}

		return Aplazame_Api_Router::success( Aplazame_Sdk_Serializer_JsonSerializer::serializeValue( $historyOrders ) );
	}
}
