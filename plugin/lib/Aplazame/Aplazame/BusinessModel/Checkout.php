<?php

/**
 * Checkout.
 */
class Aplazame_Aplazame_BusinessModel_Checkout {

	public static function createFromOrder( WC_Order $order, $checkout_url, $type ) {
		$apiRouter = WC()->api_request_url( 'aplazame' );

		$merchant                       = new stdClass();
		$merchant->ko_url               = html_entity_decode( $order->get_cancel_order_url() );
		$merchant->dismiss_url          = html_entity_decode( $checkout_url );
		$merchant->success_url          = html_entity_decode( $order->get_checkout_order_received_url() );
		$merchant->pending_url          = $merchant->success_url;
		$merchant->notification_url     = add_query_arg( array( 'path' => '/confirm/' ), $apiRouter );
		$merchant->customer_history_url = add_query_arg( array( 'path' => '/order/history/' ), $apiRouter );

		$checkout           = new self();
		$checkout->toc      = true;
		$checkout->merchant = $merchant;
		$checkout->order    = Aplazame_Aplazame_BusinessModel_Order::crateFromOrder( $order );
		$checkout->customer = Aplazame_Aplazame_BusinessModel_Customer::createFromOrder( $order );
		$checkout->billing  = Aplazame_Aplazame_BusinessModel_Address::createFromOrder( $order, 'billing' );

		if ( Aplazame_Aplazame_BusinessModel_ShippingInfo::hasOrderShippingInfo( $order ) ) {
			$checkout->shipping = Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromOrder( $order );
		}

		$checkout->meta    = Aplazame_Aplazame_BusinessModel_Meta::create();
		$checkout->product = array( 'type' => $type );

		return $checkout;
	}
}
