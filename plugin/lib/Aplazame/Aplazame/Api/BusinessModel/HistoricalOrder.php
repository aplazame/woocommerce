<?php

class Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder {

	public static function createFromOrder( WC_Order $order ) {
		$order_id = WC_Aplazame::_m_or_a( $order, 'get_id', 'id' );
		$status   = $order->get_status();

		if ( method_exists( $order, 'get_date_created' ) ) {
			$order_date = $order->get_date_created();
		} else {
			$order_date = new DateTime( $order->order_date );
		}

		switch ( $status ) {
			case 'cancelled':
			case 'refunded':
				$payment_status = $status;
				break;
			case 'completed':
			case 'processing':
				$payment_status = 'payed';
				break;
			case 'failed':
				$payment_status = 'cancelled';
				$status         = 'cancelled';
				break;
			case 'on-hold':
			case 'pending':
				$payment_status = 'pending';
				$status         = 'payment';
				break;
			default:
				$payment_status = 'unknown';
				$status         = 'custom_' . $status;
		}

		$serialized = array(
			'customer' => Aplazame_Aplazame_BusinessModel_Customer::createFromOrder( $order ),
			'order'    => Aplazame_Aplazame_BusinessModel_Order::crateFromOrder( $order, $order_date ),
			'billing'  => Aplazame_Aplazame_BusinessModel_Address::createFromOrder( $order, 'billing' ),
			'meta'     => Aplazame_Aplazame_BusinessModel_Meta::create(),
			'payment'  => array(
				'method' => $order->get_payment_method(),
				'status' => $payment_status,
			),
			'status'   => $status,
		);

		if ( Aplazame_Aplazame_BusinessModel_ShippingInfo::hasOrderShippingInfo( $order ) ) {
			$serialized['shipping'] = Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromOrder( $order );
		}

		return $serialized;
	}
}
