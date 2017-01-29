<?php

class Aplazame_Sdk_Http_CurlClient implements Aplazame_Sdk_Http_ClientInterface {

	public function __construct() {
		if ( ! function_exists( 'curl_init' ) ) {
			throw new \LogicException( 'cURL extension is not loaded' );
		}
	}

	public function send( Aplazame_Sdk_Http_RequestInterface $request ) {
		$rawHeaders = array();
		foreach ( $request->getHeaders() as $header => $value ) {
			$rawHeaders[] = sprintf( '%s:%s', $header, implode( ', ', $value ) );
		}

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $request->getUri() );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $request->getMethod() );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $rawHeaders );

		$body = $request->getBody();
		if ( ! empty( $body ) ) {
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
		}

		$responseBody = curl_exec( $ch );

		if ( false === $responseBody ) {
			$message = curl_error( $ch );
			$code = curl_errno( $ch );

			curl_close( $ch );

			throw new RuntimeException( $message, $code );
		}

		$response = new Aplazame_Sdk_Http_Response(
			curl_getinfo( $ch, CURLINFO_HTTP_CODE ),
			$responseBody
		);

		curl_close( $ch );

		return $response;
	}
}
