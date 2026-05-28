<?php

/** SDK API request class */
class Aplazame_Sdk_Api_ApiRequest extends Aplazame_Sdk_Http_Request {

	const SDK_VERSION = '0.2.2';
	const FORMAT_JSON = 'json';
	const FORMAT_XML  = 'xml';
	const FORMAT_YAML = 'yaml';

	/**
	 * Create auth header
	 *
	 * @param string $access_token .
	 *
	 * @return string
	 */
	public static function createAuthorizationHeader( string $access_token ): string {
		return 'Bearer ' . $access_token;
	}

	/**
	 * Create Accept header
	 *
	 * @param bool   $use_sandbox .
	 * @param int    $api_version .
	 * @param string $format .
	 *
	 * @return string
	 */
	public static function createAcceptHeader( $use_sandbox, $api_version, $format ): string {
		$header = 'application/vnd.aplazame';
		if ( $use_sandbox ) {
			$header .= '.sandbox';
		}
		$header .= sprintf( '.v%d+%s', $api_version, $format );

		return $header;
	}

	/**
	 * Construct
	 *
	 * @param bool       $use_sandbox .
	 * @param int        $api_version The API version of the request.
	 * @param string     $access_token The Access Token of the request (Public API key or Private API key).
	 * @param string     $method The HTTP method of the request.
	 * @param string     $uri The URI of the request.
	 * @param mixed|null $data The data of the request.
	 *
	 * @throws DomainException .
	 */
	public function __construct(
		$use_sandbox,
		$api_version,
		$access_token,
		$method,
		string $uri,
		mixed $data = null
	) {
		global $wp_version;

		$headers = array(
			'Accept'          => array( self::createAcceptHeader( $use_sandbox, $api_version, self::FORMAT_JSON ) ),
			'Authorization'   => array( self::createAuthorizationHeader( $access_token ) ),
			'User-Agent'      => array(
				'Sdk/' . self::SDK_VERSION,
				'PHP/' . PHP_VERSION,
				'WordPress/' . $wp_version,
				'WooCommerce/' . WC()->version,
				'AplazameWooCommerce/' . WC_Aplazame::VERSION,
			),
			'Accept-Language' => array( 'es' ),
		);

		if ( $data && ! is_string( $data ) ) {
			$data = wp_json_encode( $data );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				throw new DomainException( json_last_error_msg(), json_last_error() );
			}
			$headers['Content-Type'] = array( 'application/json' );
		}

		parent::__construct( $method, $uri, $headers, $data );
	}
}
