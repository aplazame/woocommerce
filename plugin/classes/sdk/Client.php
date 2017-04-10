<?php

class Aplazame_Client {
	/**
	 * @var Aplazame_Sdk_Api_Client
	 */
	public $apiClient;

	/**
	 * @param string $apiBaseUri
	 * @param bool   $sandbox
	 * @param string $private_api_key
	 */
	public function __construct( $apiBaseUri, $sandbox, $private_api_key ) {
		include_once( __DIR__ . '/../../lib/Aplazame/Aplazame/Http/WpClient.php' );

		$this->apiClient = new Aplazame_Sdk_Api_Client(
			getenv( 'APLAZAME_API_BASE_URI' ) ? getenv( 'APLAZAME_API_BASE_URI' ) : 'https://api.aplazame.com',
			$sandbox ? Aplazame_Sdk_Api_Client::ENVIRONMENT_SANDBOX : Aplazame_Sdk_Api_Client::ENVIRONMENT_PRODUCTION,
			$private_api_key,
			new Aplazame_Aplazame_Http_WpClient()
		);
	}

	/**
	 * @param int        $order_id
	 * @param string     $method
	 * @param string     $path
	 * @param null|array $data
	 *
	 * @return array
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException if response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiClientException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiServerException if request is invalid.
	 */
	protected function order_request( $order_id, $method, $path, $data = null ) {
		return $this->request( $method, '/orders/' . $order_id . $path, $data );
	}

	/**
	 * @param int $order_id
	 *
	 * @return array
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException if response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiClientException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiServerException if request is invalid.
	 */
	public function authorize( $order_id ) {
		return $this->order_request( $order_id, 'POST', '/authorize' );
	}

	/**
	 * @param int $order_id
	 * @param int $amount
	 *
	 * @return array
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException if response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiClientException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiServerException if request is invalid.
	 */
	public function refund( $order_id, $amount ) {
		$amount = Aplazame_Sdk_Serializer_Decimal::fromFloat( $amount );

		return $this->order_request( $order_id, 'POST', '/refund', array(
			'amount' => $amount->jsonSerialize(),
		) );
	}

	/**
	 * @param int $order_id
	 *
	 * @return array
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException if response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiClientException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiServerException if request is invalid.
	 */
	public function cancel( $order_id ) {
		return $this->order_request( $order_id, 'POST', '/cancel' );
	}

	/**
	 * @param int $order_id
	 *
	 * @return array
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException if response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiClientException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiServerException if request is invalid.
	 */
	public function fetch( $order_id ) {
		$orders = $this->request( 'GET', '/orders?mid=' . $order_id );

		return $orders['results'][0];
	}
	/**
	 * @param string     $method The HTTP method of the request.
	 * @param string     $path The path of the request.
	 * @param array|null $data The data of the request.
	 *
	 * @return array The data of the response.
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException if response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiClientException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiServerException if request is invalid.
	 */
	public function request( $method, $path, array $data = null ) {
		try {
			return $this->apiClient->request( $method, $path, $data );
		} catch (Aplazame_Sdk_Api_ApiClientException $e) {
			$details = json_encode( $e->getError() );

			WC_Aplazame::log( "Error: {$path}; {$e->getStatusCode()}; {$details}" );

			throw $e;
		} catch (Aplazame_Sdk_Api_ApiServerException $e) {
			$details = json_encode( $e->getError() );

			WC_Aplazame::log( "Error: {$path}; {$e->getStatusCode()}; {$details}" );

			throw $e;
		} catch (Exception $e) {
			$exceptionClass = get_class( $e );

			WC_Aplazame::log( "Error: {$path} {$exceptionClass} {$e->getMessage()}" );

			throw $e;
		}
	}
}
