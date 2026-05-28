<?php

/** API Historical Class */
class Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder {
	/**
	 * Create from order
	 *
	 * @param WC_Order $order .
	 *
	 * @return array
	 * @throws DateMalformedStringException .
	 */
	public static function create_from_order( WC_Order $order ): array {
		$status = $order->get_status();

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
			'customer' => Aplazame_Aplazame_BusinessModel_Customer::create_from_order( $order ),
			'order'    => Aplazame_Aplazame_BusinessModel_Order::crate_from_order( $order, $order_date ),
			'billing'  => Aplazame_Aplazame_BusinessModel_Address::create_from_order( $order, 'billing' ),
			'meta'     => Aplazame_Aplazame_BusinessModel_Meta::create(),
			'payment'  => array(
				'method' => WC_Aplazame::method_or_attribute( $order, 'get_payment_method', 'payment_method' ),
				'status' => $payment_status,
			),
			'status'   => $status,
		);

		if ( Aplazame_Aplazame_BusinessModel_ShippingInfo::has_order_shipping_info( $order ) ) {
			$serialized['shipping'] = Aplazame_Aplazame_BusinessModel_ShippingInfo::create_from_order( $order );
		}

		return $serialized;
	}
}
