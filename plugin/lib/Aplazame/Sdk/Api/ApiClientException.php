<?php

/**
 * Exception thrown for HTTP 4xx client errors.
 */
class Aplazame_Sdk_Api_ApiClientException extends LogicException implements Aplazame_Sdk_Api_AplazameExceptionInterface {

	/**
	 * @param Aplazame_Sdk_Http_ResponseInterface $response
	 *
	 * @return Aplazame_Sdk_Api_ApiClientException
	 */
	public static function fromResponse( Aplazame_Sdk_Http_ResponseInterface $response ) {
		$responseBody = (string) $response->getBody();
		if ( empty( $responseBody ) ) {
			return new self( $response->getStatusCode(), $response->getReasonPhrase() );
		}

		$decodedBody = json_decode( $responseBody, true );
		if ( ! isset( $decodedBody['error'] ) ) {
			return new self( $response->getStatusCode(), $response->getReasonPhrase() );
		}

		$error = $decodedBody['error'];

		return new self( $response->getStatusCode(), $error['message'], $error['type'], $error );
	}

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var array
	 */
	private $error;

	/**
	 * @param string $statusCode
	 * @param string $message
	 * @param string $type
	 * @param array  $error
	 */
	public function __construct( $statusCode, $message, $type = '', array $error = array() ) {
		parent::__construct( $message, $statusCode );

		$this->type = $type;
		$this->error = $error;
	}

	public function getStatusCode() {
		return $this->getCode();
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return array
	 */
	public function getError() {
		return $this->error;
	}
}
