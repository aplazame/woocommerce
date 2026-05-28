<?php
/**
 * Client SDK
 *
 * @package WC_Aplazame/Classes/Sdk
 */

/** Client class */
class Aplazame_Client {
	/**
	 * API client
	 *
	 * @var Aplazame_Sdk_Api_Client
	 */
	public Aplazame_Sdk_Api_Client $api_client;

	/**
	 * Construct
	 *
	 * @param string $api_base_uri .
	 * @param mixed  $sandbox .
	 * @param string $private_api_key .
	 */
	public function __construct( string $api_base_uri, mixed $sandbox, string $private_api_key ) {
		include_once __DIR__ . '/../../lib/Aplazame/Aplazame/Http/WpClient.php';

		$this->api_client = new Aplazame_Sdk_Api_Client(
			$api_base_uri,
			$sandbox ? Aplazame_Sdk_Api_Client::ENVIRONMENT_SANDBOX : Aplazame_Sdk_Api_Client::ENVIRONMENT_PRODUCTION,
			$private_api_key,
			new Aplazame_Aplazame_Http_WpClient()
		);
	}

	/**
	 * Order request
	 *
	 * @param int        $order_id .
	 * @param string     $method .
	 * @param string     $path .
	 * @param mixed|null $data .
	 *
	 * @return array
	 *
	 * @throws Aplazame_Sdk_Api_ApiClientException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiServerException|Exception If request is invalid.
	 */
	protected function order_request( int $order_id, string $method, string $path, mixed $data = null ): array {
		return $this->request( $method, '/orders/' . $order_id . $path, $data );
	}

	/**
	 * Refund
	 *
	 * @param int $order_id .
	 * @param int $amount .
	 *
	 * @return array
	 *
	 * @throws Aplazame_Sdk_Api_ApiClientException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiServerException|Exception If request is invalid.
	 */
	public function refund( int $order_id, int $amount ): array {
		$amount = Aplazame_Sdk_Serializer_Decimal::fromFloat( $amount );

		return $this->order_request(
			$order_id,
			'POST',
			'/refund-extended',
			array(
				'amount' => $amount->json_serialize(),
			)
		);
	}

	/**
	 * Create checkout
	 *
	 * @param mixed $payload .
	 * @param int   $api_version .
	 *
	 * @return array
	 *
	 * @throws Aplazame_Sdk_Api_ApiClientException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiServerException|Exception If request is invalid.
	 */
	public function create_checkout( mixed $payload, int $api_version ): array {
		return $this->request( 'POST', '/checkout', $payload, $api_version );
	}

	/**
	 * Fetch
	 *
	 * @param int $order_id .
	 *
	 * @return array
	 *
	 * @throws Aplazame_Sdk_Api_ApiClientException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiServerException|Exception If request is invalid.
	 */
	public function fetch( int $order_id ): array {
		$orders = $this->request( 'GET', '/orders?mid=' . $order_id );

		return array_shift( $orders['results'] );
	}

	/**
	 * Request
	 *
	 * @param string     $method The HTTP method of the request.
	 * @param string     $path The path of the request.
	 * @param mixed|null $data The data of the request.
	 * @param int        $api_version The API version of the request.
	 *
	 * @return array The data of the response.
	 *
	 * @throws Aplazame_Sdk_Api_ApiClientException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiServerException If request is invalid.
	 * @throws Exception .
	 */
	public function request( string $method, string $path, mixed $data = null, int $api_version = 1 ): array {
		try {
			return $this->api_client->request( $method, $path, $data, $api_version );
		} catch ( Aplazame_Sdk_Api_ApiClientException $e ) {
			$details = wp_json_encode( $e->getError() );

			WC_Aplazame::log( "Error: {$path}; {$e->getStatusCode()}; {$details}" );

			throw $e;
		} catch ( Aplazame_Sdk_Api_ApiServerException $e ) {
			$details = wp_json_encode( $e->getError() );

			WC_Aplazame::log( "Error: {$path}; {$e->getStatusCode()}; {$details}" );

			throw $e;
		} catch ( Exception $e ) {
			$exception_class = get_class( $e );

			WC_Aplazame::log( "Error: {$path} {$exception_class} {$e->getMessage()}" );

			throw $e;
		}
	}
}
