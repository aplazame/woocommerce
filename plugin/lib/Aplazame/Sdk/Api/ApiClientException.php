<?php

/**
 * Exception thrown for HTTP 4xx client errors.
 */
class Aplazame_Sdk_Api_ApiClientException extends LogicException implements Aplazame_Sdk_Api_AplazameExceptionInterface {

	/**
	 * From response
	 *
	 * @param Aplazame_Sdk_Http_ResponseInterface $response .
	 *
	 * @return Aplazame_Sdk_Api_ApiClientException
	 */
	public static function fromResponse( Aplazame_Sdk_Http_ResponseInterface $response ): Aplazame_Sdk_Api_ApiClientException {
		$response_body = $response->get_body();
		if ( empty( $response_body ) ) {
			return new self( $response->get_status_code(), $response->get_reason_phrase() );
		}

		$decoded_body = json_decode( $response_body, true );
		if ( ! isset( $decoded_body['error'] ) ) {
			return new self( $response->get_status_code(), $response->get_reason_phrase() );
		}

		$error = $decoded_body['error'];

		return new self( $response->get_status_code(), $error['message'], $error['type'], $error );
	}

	/**
	 * Type
	 *
	 * @var string
	 */
	private string $type;

	/**
	 * Error
	 *
	 * @var array
	 */
	private array $error;

	/**
	 * Construct
	 *
	 * @param string $status_code .
	 * @param string $message .
	 * @param string $type .
	 * @param array  $error .
	 */
	public function __construct( string $status_code, $message, $type = '', array $error = array() ) {
		parent::__construct( $message, $status_code );

		$this->type  = $type;
		$this->error = $error;
	}

	/**
	 * Get status code
	 *
	 * @return int|mixed
	 */
	public function getStatusCode(): mixed {
		return $this->getCode();
	}

	/**
	 * Get type
	 *
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}

	/**
	 * Get error
	 *
	 * @return array
	 */
	public function getError(): array {
		return $this->error;
	}
}
