<?php

class Aplazame_Exception extends Exception {
	/**
	 * @var stdClass
	 */
	private $body;
	/**
	 * @var int
	 */
	private $status_code;
	/**
	 * @var stdClass
	 */
	private $error;

	/**
	 * @param int      $status_code
	 * @param stdClass $body
	 */
	public function __construct( $status_code, $body ) {
		parent::__construct( 'aplazame_api_error' );

		$this->status_code = $status_code;
		$this->body        = $body;
		$this->error       = $body->error;
	}

	/**
	 * @return int
	 */
	public function get_status_code() {

		return $this->status_code;
	}

	/**
	 * @return stdClass
	 */
	public function get_body() {

		return $this->body;
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	public function get_field_error( $field ) {
		$error_list = $this->error->fields->$field;

		if ( empty( $error_list ) ) {
			$error = $this->error->message;
		} else {
			$error = $error_list[0];
		}

		return $error;
	}
}

class Aplazame_Client {
	/**
	 * @param string $apiBaseUri
	 * @param bool   $sandbox
	 * @param string $private_api_key
	 */
	public function __construct( $apiBaseUri, $sandbox, $private_api_key ) {
		$this->apiBaseUri      = $apiBaseUri;
		$this->sandbox         = $sandbox;
		$this->private_api_key = $private_api_key;
		$this->version         = 1;
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	protected function endpoint( $path ) {
		return $this->apiBaseUri . $path;
	}

	/**
	 * @return string[]
	 */
	protected function headers() {
		global $wp_version;

		$versions = array(
			'PHP/' . PHP_VERSION,
			'WordPress/' . $wp_version,
			'WooCommerce/' . WC()->version,
			'AplazameWooCommerce/' . WC_Aplazame::VERSION,
		);

		return array(
			'Accept'        => 'application/vnd.aplazame.' .
			                   ( $this->sandbox ? 'sandbox.' : '' ) .
			                   'v' . $this->version .
			                   '+json',
			'Authorization' => 'Bearer ' . $this->private_api_key,
			'User-Agent'    => implode( ', ', $versions ),
			'Content-Type'  => 'application/json',
		);
	}

	/**
	 * @param string     $method
	 * @param string     $path
	 * @param null|array $data
	 *
	 * @return stdClass
	 * @throws Aplazame_Exception
	 * @throws Exception
	 */
	public function request( $method, $path, $data = null ) {
		$args = array(
			'headers' => $this->headers(),
			'method'  => $method,
		);
		if ( $data ) {
			$args['body'] = json_encode( $data );
		}

		$response = wp_remote_request( $this->endpoint( $path ), $args );

		if ( is_wp_error( $response ) ) {
			throw new Exception( 'aplazame_client_error' );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ( $status_code < 200 ) || ( $status_code >= 300 ) ) {
			throw new Aplazame_Exception( $status_code, $body );
		}

		return $body;
	}

	/**
	 * @param int        $order_id
	 * @param string     $method
	 * @param string     $path
	 * @param null|array $data
	 *
	 * @return stdClass
	 * @throws Aplazame_Exception
	 * @throws Exception
	 */
	protected function order_request( $order_id, $method, $path, $data = null ) {
		return $this->request( $method, '/orders/' . $order_id . $path, $data );
	}

	/**
	 * @param int $order_id
	 *
	 * @return stdClass
	 */
	public function authorize( $order_id ) {
		return $this->order_request( $order_id, 'POST', '/authorize' );
	}

	/**
	 * @param int $order_id
	 * @param int $amount
	 *
	 * @return stdClass
	 */
	public function refund( $order_id, $amount ) {
		return $this->order_request( $order_id, 'POST', '/refund', array(
			'amount' => $amount,
		) );
	}

	/**
	 * @param int $order_id
	 *
	 * @return stdClass
	 */
	public function cancel( $order_id ) {
		return $this->order_request( $order_id, 'POST', '/cancel' );
	}

	/**
	 * @param int $order_id
	 *
	 * @return stdClass
	 */
	public function fetch( $order_id ) {
		$orders = $this->request( 'GET', '/orders?mid=' . $order_id );

		return $orders->results[0];
	}
}
