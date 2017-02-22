<?php

/**
 * Shipping info.
 */
class Aplazame_Aplazame_BusinessModel_ShippingInfo {

	public static function createFromOrder( WC_Order $order ) {
		$shippingInfo = new self();
	    $shippingInfo->first_name = $order->shipping_first_name;
	    $shippingInfo->last_name = $order->shipping_last_name;
	    $shippingInfo->street = $order->shipping_address_1;
	    $shippingInfo->city = $order->shipping_city;
	    $shippingInfo->state = $order->shipping_state;
	    $shippingInfo->country = $order->shipping_country;
	    $shippingInfo->postcode = $order->shipping_postcode;
		$shippingInfo->address_addition = $order->shipping_address_2;
		$shippingInfo->name = $order->get_shipping_method();
		$shippingInfo->price = Aplazame_Sdk_Serializer_Decimal::fromFloat( $order->get_total_shipping() );
		$shippingInfo->phone = $order->shipping_phone;
	    if ( $order->get_total_shipping() > 0 ) {
			$shippingInfo->tax_rate = Aplazame_Sdk_Serializer_Decimal::fromFloat( 100 * $order->order_shipping_tax / $order->get_total_shipping() );
		}

		return $shippingInfo;
	}

	public static function hasOrderShippingInfo( WC_Order $order ) {
		$shipping_method = $order->get_shipping_method();

		return ( ! empty( $shipping_method ) );
	}
}
