<?php

class Aplazame_Api_Router {
	/**
	 * @return bool
	 */
	public static function verify_authentication( $expectedPrivateKey ) {
		$authorization = self::getHeaderAuthorization();
		if ( ! $authorization ) {
			return false;
		}

		return ( $authorization === $expectedPrivateKey );
	}

	private static function getHeaderAuthorization() {
		if ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
			$headers = array_change_key_case( $headers, CASE_LOWER );
		} else {
			$headers = self::getallheaders();
		}

		if ( isset( $headers['authorization'] ) ) {
			return trim( str_replace( 'Bearer', '', $headers['authorization'] ) );
		}

		return false;
	}

	private static function getallheaders() {
		$headers = '';
		foreach ( $_SERVER as $name => $value ) {
			if ( substr( $name, 0, 5 ) == 'HTTP_' ) {
				$headers[ str_replace( ' ', '-', strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ] = $value;
			}
		}

		return $headers;
	}

	public static function forbidden() {
		return array(
			'status_code' => 403,
			'payload'     => array(
				'status' => 403,
				'type'   => 'FORBIDDEN',
			),
		);
	}

	public static function not_found() {
		return array(
			'status_code' => 404,
			'payload'     => array(
				'status' => 404,
				'type'   => 'NOT_FOUND',
			),
		);
	}

	public static function collection( $page, $page_size, array $elements ) {
		return array(
			'status_code' => 200,
			'payload'     => array(
				'query'    => array(
					'page'      => $page,
					'page_size' => $page_size,
				),
				'elements' => $elements,
			),
		);
	}

	/**
	 * @var string
	 */
	private $private_api_key;

	/**
	 * @param string $private_api_key
	 */
	public function __construct( $private_api_key ) {
		if ( empty( $private_api_key ) ) {
			throw new InvalidArgumentException( 'Aplazame Private API Key is required' );
		}

		$this->private_api_key = $private_api_key;
	}

	/**
	 * @param string $path
	 * @param array  $pathArguments
	 * @param array  $queryArguments
	 *
	 * @return void
	 */
	public function process( $path, array $pathArguments, array $queryArguments ) {
		$response = $this->route( $path, $pathArguments, $queryArguments );

		status_header( $response['status_code'] );

		wp_send_json( $response['payload'] );
	}

	/**
	 * @param string $path
	 * @param array  $pathArguments
	 * @param array  $queryArguments
	 *
	 * @return array
	 */
	public function route( $path, array $pathArguments, array $queryArguments ) {
		if ( ! self::verify_authentication( $this->private_api_key ) ) {
			return self::forbidden();
		}

		switch ( $path ) {
			case '/article/':
				include_once( 'Aplazame_Api_ArticleController.php' );
				$controller = new Aplazame_Api_ArticleController();

				return $controller->articles( $queryArguments );
			case '/order/{order_id}/history/':
				include_once( 'Aplazame_Api_OrderController.php' );
				$controller = new Aplazame_Api_OrderController();

				return $controller->history( $pathArguments, $queryArguments );
			default:
				return self::not_found();
		}
	}
}
