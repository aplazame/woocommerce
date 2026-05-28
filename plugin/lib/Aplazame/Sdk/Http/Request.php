<?php

/** SDK HTTP request */
class Aplazame_Sdk_Http_Request implements Aplazame_Sdk_Http_RequestInterface {

	/**
	 * Method
	 *
	 * @var string
	 */
	private string $method;

	/**
	 * Uri
	 *
	 * @var string
	 */
	private string $uri;

	/**
	 * Headers
	 *
	 * @var array
	 */
	private array $headers;

	/**
	 * Body
	 *
	 * @var mixed
	 */
	private mixed $body;

	/**
	 * Construct
	 *
	 * @param string $method The HTTP method of the request.
	 * @param string $uri The URI of the request.
	 * @param array  $headers The headers of the request.
	 * @param mixed  $body The body of the message.
	 */
	public function __construct( string $method, string $uri, array $headers = array(), mixed $body = '' ) {
		$this->method  = strtoupper( $method );
		$this->uri     = $uri;
		$this->headers = $headers;
		$this->body    = $body;
	}

	/**
	 * Get method
	 *
	 * @return string
	 */
	public function get_method(): string {
		return $this->method;
	}

	/**
	 * Get headers
	 *
	 * @return array
	 */
	public function get_headers(): array {
		return $this->headers;
	}

	/**
	 * Get uri
	 *
	 * @return string
	 */
	public function get_uri(): string {
		return $this->uri;
	}

	/**
	 * Get body
	 *
	 * @return mixed
	 */
	public function get_body(): mixed {
		return $this->body;
	}
}
