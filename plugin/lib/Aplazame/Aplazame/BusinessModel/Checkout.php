<?php

/**
 * Checkout.
 */
class Aplazame_Aplazame_BusinessModel_Checkout {

	public static function createFromOrder( WC_Order $order, $checkout_url ) {
		$apiRouter = WC()->api_request_url( 'aplazame' );

		$merchant = new stdClass();
		$merchant->cancel_url = html_entity_decode( $order->get_cancel_order_url() );
		$merchant->checkout_url = html_entity_decode( $checkout_url );
		$merchant->success_url = html_entity_decode( $order->get_checkout_order_received_url() );
		$merchant->pending_url = $merchant->success_url;
		$merchant->notification_url = add_query_arg( array( 'path' => '/confirm/' ), $apiRouter );
		$merchant->history_url = add_query_arg( array( 'path' => '/order/{order_id}/history/' ), $apiRouter );

		$checkout = new self();
		$checkout->toc = true;
		$checkout->merchant = $merchant;
		$checkout->order = Aplazame_Aplazame_BusinessModel_Order::crateFromOrder( $order );
		$checkout->customer = Aplazame_Aplazame_BusinessModel_Customer::createFromOrder( $order );
		$checkout->billing = Aplazame_Aplazame_BusinessModel_Address::createFromOrder( $order, 'billing' );

		if ( Aplazame_Aplazame_BusinessModel_ShippingInfo::hasOrderShippingInfo( $order ) ) {
			$checkout->shipping = Aplazame_Aplazame_BusinessModel_ShippingInfo::createFromOrder( $order );
		}

		$checkout->meta = array(
	        'module' => array(
		        'name'    => 'aplazame:woocommerce',
		        'version' => WC_Aplazame::VERSION,
	        ),
			'version' => WC()->version,
		);

		return $checkout;
	}
}
