<?php

if ( ! function_exists( 'json_last_error_msg' ) ) {
	include __DIR__ . '/../json_polyfill.php';
}

/** SDK API client class */
class Aplazame_Sdk_Api_Client {

	const ENVIRONMENT_PRODUCTION = 'production';
	const ENVIRONMENT_SANDBOX    = 'sandbox';

	/**
	 * API base URI
	 *
	 * @var string
	 */
	private string $api_base_uri;

	/**
	 * Use sandbox
	 *
	 * @var bool
	 */
	private bool $use_sandbox;

	/**
	 * Access token
	 *
	 * @var string
	 */
	private string $access_token;

	/**
	 * Client interface
	 *
	 * @var Aplazame_Sdk_Http_ClientInterface
	 */
	private Aplazame_Sdk_Http_ClientInterface $http_client;

	/**
	 * Construct
	 *
	 * @param string                                 $api_base_uri The API base URI.
	 * @param string                                 $environment Destination of the request.
	 * @param string                                 $access_token The Access Token of the request (Public API key or Private API key).
	 * @param Aplazame_Sdk_Http_ClientInterface|null $http_client .
	 */
	public function __construct(
		string $api_base_uri,
		string $environment,
		string $access_token,
		Aplazame_Sdk_Http_ClientInterface $http_client = null
	) {
		$this->api_base_uri = $api_base_uri;
		$this->use_sandbox  = ( self::ENVIRONMENT_SANDBOX === $environment ) ? true : false;
		$this->access_token = $access_token;
		$this->http_client  = $http_client ?? new Aplazame_Sdk_Http_CurlClient();
	}

	/**
	 * Performs a DELETE request.
	 *
	 * @param string $path The path of the request.
	 *
	 * @return array The data of the response.
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException If response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiServerException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiClientException If request is invalid.
	 */
	public function delete( string $path ): array {
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
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException If response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiServerException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiClientException If request is invalid.
	 */
	public function get( string $path, array $query = array() ): array {
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
	 * @param mixed  $data The data of the request.
	 *
	 * @return array The data of the response.
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException If response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiServerException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiClientException If request is invalid.
	 */
	public function patch( string $path, mixed $data ): array {
		return $this->request( 'PATCH', $path, $data );
	}

	/**
	 * Performs a POST request.
	 *
	 * @param string $path The path of the request.
	 * @param mixed  $data The data of the request.
	 *
	 * @return array The data of the response.
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException If response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiServerException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiClientException If request is invalid.
	 */
	public function post( string $path, mixed $data ): array {
		return $this->request( 'POST', $path, $data );
	}

	/**
	 * Performs a PUT request.
	 *
	 * @param string $path The path of the request.
	 * @param mixed  $data The data of the request.
	 *
	 * @return array The data of the response.
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_DeserializeException If response cannot be deserialized.
	 * @throws Aplazame_Sdk_Api_ApiServerException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiClientException If request is invalid.
	 */
	public function put( string $path, mixed $data ): array {
		return $this->request( 'PUT', $path, $data );
	}

	/**
	 * Request function
	 *
	 * @param string     $method The HTTP method of the request.
	 * @param string     $path The path of the request.
	 * @param mixed|null $data The data of the request.
	 * @param int        $api_version The API version of the request.
	 *
	 * @return array The data of the response.
	 *
	 * @throws Aplazame_Sdk_Api_ApiCommunicationException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiClientException If an I/O error occurs.
	 * @throws Aplazame_Sdk_Api_ApiServerException If request is invalid.
	 */
	public function request( string $method, string $path, mixed $data = null, int $api_version = 1 ): array {
		$uri = $this->api_base_uri . '/' . ltrim( $path, '/' );

		$request = new Aplazame_Sdk_Api_ApiRequest( $this->use_sandbox, $api_version, $this->access_token, $method, $uri, $data );
		try {
			$response = $this->http_client->send( $request );
		} catch ( RuntimeException $e ) {
			throw Aplazame_Sdk_Api_ApiCommunicationException::fromException( $e );
		}

		if ( $response->get_status_code() >= 500 ) {
			throw Aplazame_Sdk_Api_ApiServerException::fromResponse( $response );
		}

		if ( $response->get_status_code() >= 400 ) {
			throw Aplazame_Sdk_Api_ApiClientException::fromResponse( $response );
		}

		return $this->decode_response_body( $response->get_body() );
	}

	/**
	 * Decode response body
	 *
	 * @param string $response_body The HTTP response body.
	 *
	 * @return array Decoded payload.
	 *
	 * @throws Aplazame_Sdk_Api_DeserializeException If response cannot be deserialized.
	 */
	protected function decode_response_body( string $response_body ): array {
		// Response body is empty for HTTP 204 and 304 status code.
		if ( empty( $response_body ) ) {
			return array();
		}

		$response_body = json_decode( $response_body, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			throw new Aplazame_Sdk_Api_DeserializeException( 'Unable to deserialize JSON data: ' . json_last_error_msg(), json_last_error() );
		}

		return $response_body;
	}
}
