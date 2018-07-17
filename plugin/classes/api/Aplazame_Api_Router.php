<?php

class Aplazame_Api_Router {
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

	public static function client_error( $detail ) {
		return array(
			'status_code' => 400,
			'payload' => array(
				'status' => 400,
				'type' => 'CLIENT_ERROR',
				'detail' => $detail,
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
	 * @var string
	 */
	private $sandbox;

	/**
	 * @param string $private_api_key
	 */
	public function __construct( $private_api_key, $sandbox ) {
		if ( empty( $private_api_key ) ) {
			throw new InvalidArgumentException( 'Aplazame Private API Key is required' );
		}

		$this->private_api_key = $private_api_key;
		$this->sandbox = $sandbox;
	}

	/**
	 * @param string     $path
	 * @param array      $pathArguments
	 * @param array      $queryArguments
	 * @param null|array $payload
	 *
	 * @return void
	 */
	public function process( $path, array $pathArguments, array $queryArguments, $payload ) {
		$response = $this->route( $path, $pathArguments, $queryArguments, $payload );

		status_header( $response['status_code'] );

		wp_send_json( $response['payload'] );
	}

	/**
	 * @param string     $path
	 * @param array      $pathArguments
	 * @param array      $queryArguments
	 * @param null|array $payload
	 *
	 * @return array
	 */
	public function route( $path, array $pathArguments, array $queryArguments, $payload ) {
		if ( ! $this->verifyAuthentication() ) {
			return self::forbidden();
		}

		switch ( $path ) {
			case '/article/':
				include_once( 'Aplazame_Api_ArticleController.php' );
				$controller = new Aplazame_Api_ArticleController();

				return $controller->articles( $queryArguments );
			case '/confirm/':
				include_once( 'Aplazame_Api_ConfirmController.php' );
				$controller = new Aplazame_Api_ConfirmController( $this->sandbox );

				return $controller->confirm( $payload );
			case '/order/{order_id}/history/':
				include_once( 'Aplazame_Api_OrderController.php' );
				$controller = new Aplazame_Api_OrderController();

				return $controller->history( $pathArguments );
			default:
				return self::not_found();
		}
	}

	/**
	 * @return bool
	 */
	private function verifyAuthentication() {
		$privateKey = $this->private_api_key;

		$authorization = $this->getAuthorizationFromRequest();
		if ( ! $authorization || empty( $privateKey ) ) {
			return false;
		}

		return ($authorization === $privateKey);
	}

	private function getAuthorizationFromRequest() {
		$token = isset( $_GET['access_token'] ) ? stripslashes_deep( $_GET['access_token'] ) : false;
		if ( $token ) {
			return $token;
		}

		return false;
	}
}
