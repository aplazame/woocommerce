<?php

final class Aplazame_Api_ConfirmController {

	private static function ok() {
		return Aplazame_Api_Router::success(
			array(
				'status_code' => 200,
				'payload' => array(
					'status' => 'ok',
				),
			)
		);
	}

	private static function ko() {
		return Aplazame_Api_Router::success(
			array(
				'status_code' => 200,
				'payload' => array(
					'status' => 'ko',
				),
			)
		);
	}

	/**
	 * @var string
	 */
	private $sandbox;

	public function __construct( $sandbox ) {
		$this->sandbox = $sandbox;
	}

	public function confirm( $payload ) {
		if ( ! $payload ) {
			return Aplazame_Api_Router::client_error( 'Payload is malformed' );
		}

		if ( ! isset( $payload['sandbox'] ) || $payload['sandbox'] !== $this->sandbox ) {
			return Aplazame_Api_Router::client_error( '"sandbox" not provided' );
		}

		if ( ! isset( $payload['mid'] ) ) {
			return Aplazame_Api_Router::client_error( '"mid" not provided' );
		}

		$order = wc_get_order( $payload['mid'] );
		if ( ! $order ) {
			return Aplazame_Api_Router::not_found();
		}

		if ( $this->isFraud( $payload, $order ) ) {
			$order->update_status( 'cancelled', sprintf( __( 'Cancelled', 'aplazame' ) ) );

			return self::ko();
		}

		switch ( $payload['status'] ) {
			case 'pending':
				switch ( $payload['status_reason'] ) {
					case 'confirmation_required':
						$order->update_status( 'processing', sprintf( __( 'Confirmed', 'aplazame' ) ) );
						break;
				}
				break;
			case 'ko':
				$order->update_status(
					'cancelled',
		            sprintf(
	                    __( 'Order has been cancelled: %s', 'aplazame' ),
	                    $payload['status_reason']
		            )
	            );
				break;
		}

		return self::ok();
	}

	private function isFraud( array $payload, WC_Order $order ) {
		return ($payload['total_amount'] !== Aplazame_Sdk_Serializer_Decimal::fromFloat( $order->get_total() )->jsonSerialize() ||
			    $payload['currency']['code'] !== $order->get_order_currency());
	}
}
