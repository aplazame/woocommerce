<?php

if ( ! function_exists( 'json_last_error_msg' ) ) {
	include __DIR__ . '/../json_polyfill.php';
}

class Aplazame_Sdk_Api_Client {

	const ENVIRONMENT_PRODUCTION = 'production';
	const ENVIRONMENT_SANDBOX = 'sandbox';

	/**
	 * @var string
	 */
	private $apiBaseUri;

	/**
	 * @var bool
	 */
	private $useSandbox;

	/**
	 * @var string
	 */
	private $accessToken;

	/**
	 * @var Aplazame_Sdk_Http_ClientInterface
	 */
	private $httpClient;

	/**
	 * @param string                                 $apiBaseUri The API base URI.
	 * @param string                                 $environment Destination of the request.
	 * @param string                                 $accessToken The Access Token of the request (Public API key or Private API key)
	 * @param Aplazame_Sdk_Http_ClientInterface|null $httpClient
	 */
	public function __construct(
		$apiBaseUri,
		$environment,
		$accessToken,
		Aplazame_Sdk_Http_ClientInterface $httpClient = null
	) {
		$this->apiBaseUri = $apiBaseUri;
		$this->useSandbox = ($environment === self::ENVIRONMENT_SANDBOX) ? true : false;
		$this->accessToken = $accessToken;
		$this->httpClient = $httpClient ? $httpClient : new Aplazame_Sdk_Http_CurlClient();
	}

	/**
	 * Performs a DELETE request.
	 *
	 * @param string $path The path of the request.
	 *
	 * @return array The data of the response.
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException if response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiServerException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiClientException if request is invalid.
	 */
	public function delete( $path ) {
		return $this->request( 'DELETE', $path );
	}

	/**
	 * Performs a GET request.
	 *
	 * @param string $path The path of the request.
	 * @param array  $query The filters of the request.
	 *
	 * @return array The data of the response.
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException if response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiServerException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiClientException if request is invalid.
	 */
	public function get( $path, array $query = array() ) {
		if ( ! empty( $query ) ) {
			$query = http_build_query( $query );
			$path .= '?' . $query;
		}

		return $this->request( 'GET', $path );
	}

	/**
	 * Performs a POST request.
	 *
	 * @param string $path The path of the request.
	 * @param array  $data The data of the request.
	 *
	 * @return array The data of the response.
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException if response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiServerException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiClientException if request is invalid.
	 */
	public function patch( $path, array $data ) {
		return $this->request( 'PATCH', $path, $data );
	}

	/**
	 * Performs a POST request.
	 *
	 * @param string $path The path of the request.
	 * @param array  $data The data of the request.
	 *
	 * @return array The data of the response.
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException if response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiServerException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiClientException if request is invalid.
	 */
	public function post( $path, array $data ) {
		return $this->request( 'POST', $path, $data );
	}

	/**
	 * Performs a PUT request.
	 *
	 * @param string $path The path of the request.
	 * @param array  $data The data of the request.
	 *
	 * @return array The data of the response.
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException if response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiServerException if an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiClientException if request is invalid.
	 */
	public function put( $path, array $data ) {
		return $this->request( 'PUT', $path, $data );
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
		$uri = $this->apiBaseUri . '/' . ltrim( $path, '/' );

		$request = new Aplazame_Sdk_Api_ApiRequest( $this->useSandbox, $this->accessToken, $method, $uri, $data );
		try {
			$response = $this->httpClient->send( $request );
		} catch (RuntimeException $e) {
			throw Aplazame_Sdk_Api_ApiCommunicationException::fromException( $e );
		}

		if ( $response->getStatusCode() >= 500 ) {
			throw Aplazame_Sdk_Api_ApiClientException::fromResponse( $response );
		}

		if ( $response->getStatusCode() >= 400 ) {
			throw Aplazame_Sdk_Api_ApiServerException::fromResponse( $response );
		}

		$payload = $this->decodeResponseBody( (string) $response->getBody() );

		return $payload;
	}

	/**
	 * @param string $responseBody The HTTP response body.
	 *
	 * @return array Decoded payload.
	 *
	 * @throws Aplazame_Sdk_Api_DeserializeException if response cannot be deserialized.
	 */
	protected function decodeResponseBody( $responseBody ) {
		// Response body is empty for HTTP 204 and 304 status code.
		if ( empty( $responseBody ) ) {
			return array();
		}

		$responseBody = json_decode( $responseBody, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new Aplazame_Sdk_Api_DeserializeException( 'Unable to deserialize JSON data: ' . json_last_error_msg(), json_last_error() );
		}

		return $responseBody;
	}
}
