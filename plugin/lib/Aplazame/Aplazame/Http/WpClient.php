<?php

class Aplazame_Aplazame_Http_WpClient implements Aplazame_Sdk_Http_ClientInterface {

	public function send( Aplazame_Sdk_Http_RequestInterface $request ) {
		$rawHeaders = array();
		foreach ( $request->getHeaders() as $header => $value ) {
			$rawHeaders[ $header ] = implode( ', ', $value );
		}

	    $args = array(
			'headers' => $rawHeaders,
			'method'  => $request->getMethod(),
			'body'    => $request->getBody(),
		);

		$wpResponse = wp_remote_request( $request->getUri(), $args );
	    if ( is_wp_error( $wpResponse ) ) {
		    throw new RuntimeException( $wpResponse->get_error_message(), (int) $wpResponse->get_error_code() );
		}

		$responseBody = wp_remote_retrieve_body( $wpResponse );

		$response = new Aplazame_Sdk_Http_Response(
	        wp_remote_retrieve_response_code( $wpResponse ),
			$responseBody
		);

		return $response;
	}
}
