<?php

class Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder {

	public static function createFromOrder( WC_Order $order ) {
		if (method_exists($order, 'get_id')) {
			$order_id = $order->get_id();
		} else {
			$order_id = $order->id;
		}

		$orderDate = new DateTime( $order->order_date );

		$serialized = array(
			'id'         => (string) $order_id,
			'amount'     => Aplazame_Sdk_Serializer_Decimal::fromFloat( $order->get_total() ),
			'due'        => '',
			'status'     => $order->get_status(),
			'type'       => Aplazame_Helpers::get_payment_method( $order_id ),
			'order_date' => $orderDate->format( DATE_ISO8601 ),
			'currency'   => $order->get_order_currency(),
			'billing'    => Aplazame_Aplazame_BusinessModel_Address::createFromOrder( $order, 'billing' ),
		);

		if ( Aplazame_Aplazame_BusinessModel_ShippingInfo::hasOrderShippingInfo( $order ) ) {
			$serialized['shipping'] = Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromOrder( $order );
		}

		return $serialized;
	}
}
