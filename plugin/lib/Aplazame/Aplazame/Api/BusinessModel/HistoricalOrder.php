<?php

class Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder {

	public static function createFromOrder( WC_Order $order ) {
		$order_id = WC_Aplazame::_m_or_a( $order, 'get_id', 'id' );

		if ( method_exists( $order, 'get_date_created' ) ) {
			$orderDate = $order->get_date_created();
		} else {
			$orderDate = new DateTime( $order->order_date );
		}

		$serialized = array(
			'id'         => (string) $order_id,
			'amount'     => Aplazame_Sdk_Serializer_Decimal::fromFloat( $order->get_total() ),
			'due'        => '',
			'status'     => $order->get_status(),
			'type'       => Aplazame_Helpers::get_payment_method( $order_id ),
			'order_date' => $orderDate->format( DATE_ISO8601 ),
			'currency'   => WC_Aplazame::_m_or_m( $order, 'get_currency', 'get_order_currency' ),
			'billing'    => Aplazame_Aplazame_BusinessModel_Address::createFromOrder( $order, 'billing' ),
		);

		if ( Aplazame_Aplazame_BusinessModel_ShippingInfo::hasOrderShippingInfo( $order ) ) {
			$serialized['shipping'] = Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromOrder( $order );
		}

		return $serialized;
	}
}
