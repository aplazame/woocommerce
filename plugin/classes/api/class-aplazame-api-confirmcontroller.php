<?php
/**
 * Confirm controller for checkout validation
 *
 * @package WC_Aplazame/Classes/Api
 */

/** Confirm controller class */
final class Aplazame_Api_ConfirmController {

	/**
	 * OK function
	 *
	 * @return array
	 */
	private static function ok(): array {
		return Aplazame_Api_Router::success(
			array(
				'status' => 'ok',
			)
		);
	}

	/**
	 * KO function
	 *
	 * @param string $reason KO reason.
	 *
	 * @return array
	 */
	private static function ko( string $reason ): array {
		return Aplazame_Api_Router::success(
			array(
				'status' => 'ko',
				'reason' => $reason,
			)
		);
	}

	/**
	 * Sandbox mode
	 *
	 * @var mixed
	 */
	private mixed $sandbox;

	/**
	 * Construct
	 *
	 * @param mixed $sandbox .
	 */
	public function __construct( mixed $sandbox ) {
		$this->sandbox = $sandbox;
	}

	/**
	 * Confirm process with payload
	 *
	 * @param mixed $payload .
	 *
	 * @return array
	 */
	public function confirm( mixed $payload ): array {
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

		if ( WC_Aplazame::method_or_attribute( $order, 'get_payment_method', 'payment_method' ) !== WC_Aplazame::METHOD_ID ) {
			return self::ko( 'Aplazame is not the current payment method' );
		}

		switch ( $payload['status'] ) {
			case 'ok':
				if ( method_exists( $order, 'payment_complete' ) ) {
					if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0', '<' ) ) {
						$order->payment_complete();
						break;
					}
					if ( ! $order->payment_complete() ) {
						return self::ko( "'payment_complete' function failed" );
					}
				} else {
					$order->update_status( 'processing', sprintf( __( 'Confirmed', 'aplazame' ) ) );
				}
				break;
			case 'ko':
				$order->update_status(
					'cancelled',
					sprintf(
						/* translators: %s: order */
						__( 'Order has been cancelled: %s', 'aplazame' ),
						$payload['status_reason']
					)
				);
				break;
		}

		return self::ok();
	}
}
