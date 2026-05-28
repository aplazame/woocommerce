<?php

/** Model Shipping info class */
class Aplazame_Aplazame_BusinessModel_ShippingInfo {
	/**
	 * Create from order
	 *
	 * @param WC_Order $order .
	 *
	 * @return self
	 */
	public static function create_from_order( WC_Order $order ): Aplazame_Aplazame_BusinessModel_ShippingInfo {
		$shipping_info                   = new self();
		$shipping_info->first_name       =
			WC_Aplazame::method_or_attribute( $order, 'get_shipping_first_name', 'shipping_first_name' );
		$shipping_info->last_name        =
			WC_Aplazame::method_or_attribute( $order, 'get_shipping_last_name', 'shipping_last_name' );
		$shipping_info->street           =
			WC_Aplazame::method_or_attribute( $order, 'get_shipping_address_1', 'shipping_address_1' );
		$shipping_info->city             = WC_Aplazame::method_or_attribute( $order, 'get_shipping_city', 'shipping_city' );
		$shipping_info->state            = WC_Aplazame::method_or_attribute( $order, 'get_shipping_state', 'shipping_state' );
		$shipping_info->country          = WC_Aplazame::method_or_attribute( $order, 'get_shipping_country', 'shipping_country' );
		$shipping_info->postcode         = WC_Aplazame::method_or_attribute( $order, 'get_shipping_postcode', 'shipping_postcode' );
		$shipping_info->address_addition =
			WC_Aplazame::method_or_attribute( $order, 'get_shipping_address_2', 'shipping_address_2' );
		$shipping_info->name             = $order->get_shipping_method();
		$total_shipping                  = WC_Aplazame::method1_or_method2( $order, 'get_shipping_total', 'get_total_shipping' );
		$shipping_info->price            = Aplazame_Sdk_Serializer_Decimal::fromFloat( $total_shipping );
		if ( $total_shipping > 0 ) {
			$shipping_tax            = WC_Aplazame::method_or_attribute( $order, 'get_shipping_tax', 'order_shipping_tax' );
			$shipping_info->tax_rate =
				Aplazame_Sdk_Serializer_Decimal::fromFloat( 100 * $shipping_tax / $total_shipping );
		}

		return $shipping_info;
	}

	/**
	 * Has order shipping info function
	 *
	 * @param WC_Order $order .
	 *
	 * @return bool
	 */
	public static function has_order_shipping_info( WC_Order $order ): bool {
		$shipping_method = $order->get_shipping_method();

		return ( ! empty( $shipping_method ) );
	}
}
