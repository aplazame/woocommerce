<?php

/**
 * Shipping info.
 */
class Aplazame_Aplazame_BusinessModel_ShippingInfo {

	public static function createFromOrder( WC_Order $order ) {
		$shippingInfo                   = new self();
		$shippingInfo->first_name       =
			WC_Aplazame::_m_or_a( $order, 'get_shipping_first_name', 'shipping_first_name' );
		$shippingInfo->last_name        =
			WC_Aplazame::_m_or_a( $order, 'get_shipping_last_name', 'shipping_last_name' );
		$shippingInfo->street           =
			WC_Aplazame::_m_or_a( $order, 'get_shipping_address_1', 'shipping_address_1' );
		$shippingInfo->city             = WC_Aplazame::_m_or_a( $order, 'get_shipping_city', 'shipping_city' );
		$shippingInfo->state            = WC_Aplazame::_m_or_a( $order, 'get_shipping_state', 'shipping_state' );
		$shippingInfo->country          = WC_Aplazame::_m_or_a( $order, 'get_shipping_country', 'shipping_country' );
		$shippingInfo->postcode         = WC_Aplazame::_m_or_a( $order, 'get_shipping_postcode', 'shipping_postcode' );
		$shippingInfo->address_addition =
			WC_Aplazame::_m_or_a( $order, 'get_shipping_address_2', 'shipping_address_2' );
		$shippingInfo->name             = $order->get_shipping_method();
		$total_shipping                 = WC_Aplazame::_m_or_m( $order, 'get_shipping_total', 'get_total_shipping' );
		$shippingInfo->price            = Aplazame_Sdk_Serializer_Decimal::fromFloat( $total_shipping );
		if ( $total_shipping > 0 ) {
			$shipping_tax           = WC_Aplazame::_m_or_a( $order, 'get_shipping_tax', 'order_shipping_tax' );
			$shippingInfo->tax_rate =
				Aplazame_Sdk_Serializer_Decimal::fromFloat( 100 * $shipping_tax / $total_shipping );
		}

		return $shippingInfo;
	}

	public static function hasOrderShippingInfo( WC_Order $order ) {
		$shipping_method = $order->get_shipping_method();

		return ( ! empty( $shipping_method ) );
	}
}
