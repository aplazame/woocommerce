<?php

/**
 * Order.
 */
class Aplazame_Aplazame_BusinessModel_Order {

	public static function crateFromOrder( WC_Order $order, $order_date = null ) {
		$aOrder               = new self();
		$aOrder->id           = WC_Aplazame::_m_or_a( $order, 'get_id', 'id' );
		$aOrder->currency     = WC_Aplazame::_m_or_m( $order, 'get_currency', 'get_order_currency' );
		$aOrder->total_amount = Aplazame_Sdk_Serializer_Decimal::fromFloat( $order->get_total() );
		$aOrder->articles     = array_map(
			array( 'Aplazame_Aplazame_BusinessModel_Article', 'createFromOrderItem' ),
			array_values( $order->get_items() )
		);
		$aOrder->discount     = Aplazame_Sdk_Serializer_Decimal::fromFloat( $order->get_total_discount() );

		if ( $order_date ) {
			$aOrder->created = Aplazame_Sdk_Serializer_Date::fromDateTime( $order_date );
		}

		return $aOrder;
	}
}
