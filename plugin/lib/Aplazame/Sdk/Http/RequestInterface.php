<?php

interface Aplazame_Sdk_Http_RequestInterface {

	/**
	 * Retrieves the HTTP method of the request.
	 *
	 * @return string Returns the request method. The return value must use uppercase letters.
	 */
	public function getMethod();

	/**
	 * Retrieves all message header values.
	 *
	 * The keys represent the header name as it will be sent over the wire, and
	 * each value is an array of strings associated with the header.
	 *
	 *     // Represent the headers as a string
	 *     foreach ($message->getHeaders() as $name => $values) {
	 *         echo $name . ": " . implode(", ", $values);
	 *     }
	 *
	 *     // Emit headers iteratively:
	 *     foreach ($message->getHeaders() as $name => $values) {
	 *         foreach ($values as $value) {
	 *             header(sprintf('%s: %s', $name, $value), false);
	 *         }
	 *     }
	 *
	 * While header names are not case-sensitive, getHeaders() will preserve the
	 * exact case in which headers were originally specified.
	 *
	 * @return array Returns an associative array of the message's headers. Each
	 *     key MUST be a header name, and each value MUST be an array of strings
	 *     for that header.
	 */
	public function getHeaders();

	/**
	 * Retrieves the URI instance.
	 *
	 * @link http://tools.ietf.org/html/rfc3986#section-4.3
	 *
	 * @return string Returns the URI of the request.
	 */
	public function getUri();

	/**
	 * Gets the body of the message.
	 *
	 * @return string Returns the body of the request.
	 */
	public function getBody();
}
