<?php

/** SDK HTTP cURL client */
class Aplazame_Sdk_Http_CurlClient implements Aplazame_Sdk_Http_ClientInterface {

	/**
	 * Construct
	 *
	 * @throws LogicException .
	 */
	public function __construct() {
		if ( ! function_exists( 'curl_init' ) ) {
			throw new LogicException( 'cURL extension is not loaded' );
		}
	}

	/**
	 * Send
	 *
	 * @param Aplazame_Sdk_Http_RequestInterface $request .
	 *
	 * @return Aplazame_Sdk_Http_ResponseInterface
	 * @throws RuntimeException .
	 */
	public function send( Aplazame_Sdk_Http_RequestInterface $request ): Aplazame_Sdk_Http_ResponseInterface {
		$raw_headers = array();
		foreach ( $request->get_headers() as $header => $value ) {
			$raw_headers[] = sprintf( '%s:%s', $header, implode( ', ', $value ) );
		}

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $request->get_uri() );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $request->get_method() );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $raw_headers );

		$body = $request->get_body();
		if ( ! empty( $body ) ) {
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
		}

		$response_body = curl_exec( $ch );

		if ( false === $response_body ) {
			$message = curl_error( $ch );
			$code    = curl_errno( $ch );

			curl_close( $ch );

			throw new RuntimeException( $message, $code );
		}

		$response = new Aplazame_Sdk_Http_Response(
			curl_getinfo( $ch, CURLINFO_HTTP_CODE ),
			$response_body
		);

		curl_close( $ch );

		return $response;
	}
}
