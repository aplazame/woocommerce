<?php

class Aplazame_History {
	/**
	 * @var string
	 */
	private $private_api_key;

	/**
	 * @param string $private_api_key
	 */
	public function __construct( $private_api_key ) {
		if ( empty( $private_api_key ) ) {
			throw new InvalidArgumentException( 'Aplazame Private API Key is required' );
		}

		$this->private_api_key = $private_api_key;
	}

	/**
	 * @return void
	 */
	public function process( $orderId ) {
		if ( ! $this->verifyAuthentication() ) {
			status_header( 403 );
			return;
		}

		$order = wc_get_order( $orderId );
		if ( ! $order ) {
			status_header( 404 );
			return;
		}

		/** @var WP_Post[] $wcOrders */
		$wcOrders = get_posts( array(
			'meta_key'    => '_billing_email',
			'meta_value'  => $order->billing_email,
			'post_type'   => 'shop_order',
			'numberposts' => - 1,
		) );

		$historyOrders = array();

		foreach ( $wcOrders as $wcOrder ) {
			$historyOrders[] = Aplazame_Aplazame_Api_BusinessModel_HistoricalOrder::createFromOrder( new WC_Order( $wcOrder->ID ) );
		}

		wp_send_json( $historyOrders );
	}

	/**
	 * @return bool
	 */
	private function verifyAuthentication() {
		$privateKey = $this->private_api_key;

		$authorization = $this->getAuthorizationFromRequest();
		if ( ! $authorization || empty( $privateKey ) ) {
			return false;
		}

		return ($authorization === $privateKey);
	}

	private function getAuthorizationFromRequest() {
		$token = isset( $_GET['access_token'] ) ? stripslashes_deep( $_GET['access_token'] ) : false;
		if ( $token ) {
			return $token;
		}

		if ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
		} else {
			$headers = $this->getallheaders();
		}
		$headers = array_change_key_case( $headers, CASE_LOWER );

		if ( isset( $headers['authorization'] ) ) {
			return trim( str_replace( 'Bearer', '', $headers['authorization'] ) );
		}

		return false;
	}

	private function getallheaders() {
		$headers = array();
		$copy_server = array(
			'CONTENT_TYPE'   => 'content-type',
			'CONTENT_LENGTH' => 'content-length',
			'CONTENT_MD5'    => 'content-md5',
		);

		foreach ( $_SERVER as $name => $value ) {
			if ( substr( $name, 0, 5 ) === 'HTTP_' ) {
				$name = substr( $name, 5 );
				if ( ! isset( $copy_server[ $name ] ) || ! isset( $_SERVER[ $name ] ) ) {
					$headers[ str_replace( ' ', '-', strtolower( str_replace( '_', ' ', $name ) ) ) ] = $value;
				}
			} elseif ( isset( $copy_server[ $name ] ) ) {
				$headers[ $copy_server[ $name ] ] = $value;
			}
		}

		if ( ! isset( $headers['authorization'] ) ) {
			if ( isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) {
				$headers['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
			}
		}

		return $headers;
	}
}
