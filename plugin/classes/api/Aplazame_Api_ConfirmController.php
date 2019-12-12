<?php

final class Aplazame_Api_ConfirmController {

	private static function ok() {
		return Aplazame_Api_Router::success(
			array(
				'status' => 'ok',
			)
		);
	}

	private static function ko( $reason ) {
		return Aplazame_Api_Router::success(
			array(
				'status' => 'ko',
				'reason' => $reason,
			)
		);
	}

	/**
	 *
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

		if ( ! in_array( $order->get_payment_method(), array( WC_Aplazame::METHOD_ID, WC_Aplazame::METHOD_ID . '_pay_later' ) ) ) {
			return self::ko( 'Aplazame is not the current payment method' );
		}

		if ( $this->isFraud( $payload, $order ) ) {
			$order->update_status( 'cancelled', sprintf( __( 'Cancelled', 'aplazame' ) ) );

			return self::ko( 'Fraud detected' );
		}

		switch ( $payload['status'] ) {
			case 'pending':
				switch ( $payload['status_reason'] ) {
					case 'confirmation_required':
						if ( method_exists( $order, 'payment_complete' ) ) {
							if ( ! $order->payment_complete() ) {
								return self::ko( "'payment_complete' function failed" );
							}
						} else {
							$order->update_status( 'processing', sprintf( __( 'Confirmed', 'aplazame' ) ) );
						}
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
		return ( $payload['total_amount'] !== Aplazame_Sdk_Serializer_Decimal::fromFloat( $order->get_total() )->jsonSerialize() ||
				$payload['currency']['code'] !== WC_Aplazame::_m_or_m( $order, 'get_currency', 'get_order_currency' ) );
	}
}
