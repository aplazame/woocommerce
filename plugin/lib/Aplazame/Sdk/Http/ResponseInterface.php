<?php

/**
 * Representation of an outgoing, server-side response.
 */
interface Aplazame_Sdk_Http_ResponseInterface {

	/**
	 * Gets the response status code.
	 *
	 * The status code is a 3-digit integer result code of the server's attempt
	 * to understand and satisfy the request.
	 *
	 * @return int Status code.
	 */
	public function getStatusCode();

	/**
	 * Gets the response reason phrase associated with the status code.
	 *
	 * Because a reason phrase is not a required element in a response
	 * status line, the reason phrase value MAY be null. Implementations MAY
	 * choose to return the default RFC 7231 recommended reason phrase (or those
	 * listed in the IANA HTTP Status Code Registry) for the response's
	 * status code.
	 *
	 * @link http://tools.ietf.org/html/rfc7231#section-6
	 * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
	 *
	 * @return string Reason phrase; must return an empty string if none present.
	 */
	public function getReasonPhrase();

	/**
	 * Gets the body of the message.
	 *
	 * @return string
	 */
	public function getBody();
}
