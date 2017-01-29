<?php

class Aplazame_Sdk_Api_ApiRequest extends Aplazame_Sdk_Http_Request {

	const SDK_VERSION = '0.2.1';
	const FORMAT_JSON = 'json';
	const FORMAT_XML = 'xml';
	const FORMAT_YAML = 'yaml';

	/**
	 * @param string $accessToken
	 *
	 * @return string
	 */
	public static function createAuthorizationHeader( $accessToken ) {
		return 'Bearer ' . $accessToken;
	}

	/**
	 * @param bool   $useSandbox
	 * @param int    $apiVersion
	 * @param string $format
	 *
	 * @return string
	 */
	public static function createAcceptHeader( $useSandbox, $apiVersion, $format ) {
		$header = 'application/vnd.aplazame';
		if ( $useSandbox ) {
			$header .= '.sandbox';
		}
		$header .= sprintf( '.v%d+%s', $apiVersion, $format );

		return $header;
	}

	/**
	 * @param bool   $useSandbox
	 * @param string $accessToken The Access Token of the request (Public API key or Private API key)
	 * @param string $method The HTTP method of the request.
	 * @param string $uri The URI of the request.
	 * @param mixed  $data The data of the request.
	 */
	public function __construct(
		$useSandbox,
		$accessToken,
		$method,
		$uri,
		$data = null
	) {
	    global $wp_version;

		$headers = array(
			'Accept' => array( self::createAcceptHeader( $useSandbox, 1, self::FORMAT_JSON ) ),
			'Authorization' => array( self::createAuthorizationHeader( $accessToken ) ),
			'User-Agent' => array(
				'Sdk/' . self::SDK_VERSION,
				'PHP/' . PHP_VERSION,
	            'WordPress/' . $wp_version,
				'WooCommerce/' . WC()->version,
				'AplazameWooCommerce/' . WC_Aplazame::VERSION,
			),
		);

		if ( $data && ! is_string( $data ) ) {
			$data = json_encode( $data );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				throw new DomainException( json_last_error_msg(), json_last_error() );
			}
			$headers['Content-Type'] = array( 'application/json' );
		}

		parent::__construct( $method, $uri, $headers, $data );
	}
}
