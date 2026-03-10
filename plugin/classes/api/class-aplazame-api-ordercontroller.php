<?php
/**
 * Order controller
 *
 * @package WC_Aplazame/Classes/Api
 */

/** Order controller class */
final class Aplazame_Api_OrderController {

	/**
	 * Order history
	 *
	 * @param array $params .
	 *
	 * @return array
	 */
	public function history( array $params ): array {
		if ( ! isset( $params['order_id'] ) ) {
			return Aplazame_Api_Router::not_found();
		}

		$order = wc_get_order( $params['order_id'] );
		if ( ! $order ) {
			return Aplazame_Api_Router::not_found();
		}

		/**
		 * Orders array
		 *
		 * @var WC_Order_Query[] $wc_orders
		 */
		$billing_email = WC_Aplazame::method_or_attribute( $order, 'get_billing_email', 'billing_email' );
		$wc_orders     = wc_get_orders(
			array(
				'billing_email' => $billing_email,
			)
		);

		$history_orders = array();

		foreach ( $wc_orders as $wc_order ) {
			$order_id         = WC_Aplazame::method_or_attribute( $wc_order, 'get_id', 'id' );
			$history_orders[] = Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder::createFromOrder( new WC_Order( $order_id ) );
		}

		return Aplazame_Api_Router::success( Aplazame_Sdk_Serializer_JsonSerializer::serializeValue( $history_orders ) );
	}
}
