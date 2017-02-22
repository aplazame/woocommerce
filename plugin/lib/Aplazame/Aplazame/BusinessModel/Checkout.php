<?php

/**
 * Checkout.
 */
class Aplazame_Aplazame_BusinessModel_Checkout {

	public static function createFromOrder( WC_Order $order, $checkout_url, $redirect_id ) {
		$merchant = new stdClass();
		$merchant->confirmation_url = add_query_arg( 'action', 'confirm', get_permalink( $redirect_id ) );
		$merchant->cancel_url = html_entity_decode( $order->get_cancel_order_url() );
		$merchant->checkout_url = html_entity_decode( $checkout_url );
		$merchant->success_url = html_entity_decode( $order->get_checkout_order_received_url() );

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
