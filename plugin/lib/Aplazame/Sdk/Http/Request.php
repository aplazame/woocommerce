<?php

class Aplazame_Sdk_Http_Request implements Aplazame_Sdk_Http_RequestInterface {

	/**
	 * @var string
	 */
	private $method;

	/**
	 * @var string
	 */
	private $uri;

	/**
	 * @var array
	 */
	private $headers;

	/**
	 * @var string
	 */
	private $body;

	/**
	 * @param string $method The HTTP method of the request.
	 * @param string $uri The URI of the request.
	 * @param array  $headers The headers of the request.
	 * @param string $body The body of the message.
	 */
	public function __construct( $method, $uri, array $headers = array(), $body = '' ) {
		$this->method = strtoupper( $method );
		$this->uri = $uri;
		$this->headers = $headers;
		$this->body = $body;
	}

	public function getMethod() {
		return $this->method;
	}

	public function getHeaders() {
		return $this->headers;
	}

	public function getUri() {
		return $this->uri;
	}

	public function getBody() {
		return $this->body;
	}
}
