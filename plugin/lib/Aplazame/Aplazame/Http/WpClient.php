<?php

/** Client class */
class Aplazame_Aplazame_Http_WpClient implements Aplazame_Sdk_Http_ClientInterface {

	/**
	 * Send
	 *
	 * @param Aplazame_Sdk_Http_RequestInterface $request .
	 *
	 * @return Aplazame_Sdk_Http_Response
	 * @throws RuntimeException .
	 */
	public function send( Aplazame_Sdk_Http_RequestInterface $request ): Aplazame_Sdk_Http_Response {
		$raw_headers = array_map(
			function ( $value ) {
				return implode( ', ', $value );
			},
			$request->getHeaders()
		);

		$args = array(
			'headers' => $raw_headers,
			'method'  => $request->getMethod(),
			'body'    => $request->getBody(),
			'timeout' => 30,
		);

		$wp_response = wp_remote_request( $request->getUri(), $args );
		if ( is_wp_error( $wp_response ) ) {
			throw new RuntimeException( $wp_response->get_error_message(), (int) $wp_response->get_error_code() );
		}

		$response_body = wp_remote_retrieve_body( $wp_response );

		return new Aplazame_Sdk_Http_Response(
			wp_remote_retrieve_response_code( $wp_response ),
			$response_body
		);
	}
}
