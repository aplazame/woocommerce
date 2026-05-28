<?php

/** Model Checkout class */
class Aplazame_Aplazame_BusinessModel_Checkout {

	/**
	 * Create from order
	 *
	 * @param WC_Order $order .
	 * @param mixed    $checkout_url .
	 *
	 * @return self
	 */
	public static function create_from_order( WC_Order $order, mixed $checkout_url ): Aplazame_Aplazame_BusinessModel_Checkout {
		$api_router = WC()->api_request_url( 'aplazame' );

		$merchant                   = new stdClass();
		$merchant->ko_url           = html_entity_decode( $order->get_cancel_order_url() );
		$merchant->dismiss_url      = html_entity_decode( $checkout_url );
		$merchant->success_url      = html_entity_decode( $order->get_checkout_order_received_url() );
		$merchant->pending_url      = $merchant->success_url;
		$merchant->notification_url = add_query_arg( array( 'path' => '/confirm/' ), $api_router );

		$checkout           = new self();
		$checkout->toc      = true;
		$checkout->merchant = $merchant;
		$checkout->order    = Aplazame_Aplazame_BusinessModel_Order::crate_from_order( $order );
		$checkout->customer = Aplazame_Aplazame_BusinessModel_Customer::create_from_order( $order );
		$checkout->billing  = Aplazame_Aplazame_BusinessModel_Address::create_from_order( $order, 'billing' );

		if ( Aplazame_Aplazame_BusinessModel_ShippingInfo::has_order_shipping_info( $order ) ) {
			$checkout->shipping = Aplazame_Aplazame_BusinessModel_ShippingInfo::create_from_order( $order );
		}

		$checkout->meta = Aplazame_Aplazame_BusinessModel_Meta::create();

		return $checkout;
	}
}
