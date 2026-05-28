<?php

/** Model Order class */
class Aplazame_Aplazame_BusinessModel_Order {
	/**
	 * Create from order
	 *
	 * @param WC_Order   $order .
	 * @param mixed|null $order_date .
	 *
	 * @return self
	 */
	public static function crate_from_order( WC_Order $order, mixed $order_date = null ): Aplazame_Aplazame_BusinessModel_Order {
		$a_order               = new self();
		$a_order->id           = WC_Aplazame::method_or_attribute( $order, 'get_id', 'id' );
		$a_order->currency     = WC_Aplazame::method1_or_method2( $order, 'get_currency', 'get_order_currency' );
		$a_order->total_amount = Aplazame_Sdk_Serializer_Decimal::fromFloat( $order->get_total() );
		$a_order->articles     = array_map(
			array( 'Aplazame_Aplazame_BusinessModel_Article', 'create_from_order_item' ),
			array_values( $order->get_items() )
		);
		$a_order->discount     = Aplazame_Sdk_Serializer_Decimal::fromFloat( $order->get_total_discount() );

		if ( $order_date ) {
			$a_order->created = Aplazame_Sdk_Serializer_Date::fromDateTime( $order_date );
		}

		return $a_order;
	}
}
