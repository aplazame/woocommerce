<?php
/**
 * API router
 *
 * @package WC_Aplazame/Classes/Api
 */

/** API router class */
class Aplazame_Api_Router {

	/**
	 * 403 Forbidden
	 *
	 * @return array
	 */
	public static function forbidden(): array {
		return array(
			'status_code' => 403,
			'payload'     => array(
				'status' => 403,
				'type'   => 'FORBIDDEN',
			),
		);
	}

	/**
	 * 404 Not found
	 *
	 * @return array
	 */
	public static function not_found(): array {
		return array(
			'status_code' => 404,
			'payload'     => array(
				'status' => 404,
				'type'   => 'NOT_FOUND',
			),
		);
	}

	/**
	 * 400 Client error
	 *
	 * @param mixed $detail .
	 *
	 * @return array
	 */
	public static function client_error( mixed $detail ): array {
		return array(
			'status_code' => 400,
			'payload'     => array(
				'status' => 400,
				'type'   => 'CLIENT_ERROR',
				'detail' => $detail,
			),
		);
	}

	/**
	 * 200 Success
	 *
	 * @param array $payload .
	 *
	 * @return array
	 */
	public static function success( array $payload ): array {
		return array(
			'status_code' => 200,
			'payload'     => $payload,
		);
	}

	/**
	 * Collection
	 *
	 * @param mixed $page .
	 * @param mixed $page_size .
	 * @param array $elements .
	 *
	 * @return array
	 */
	public static function collection( mixed $page, mixed $page_size, array $elements ): array {
		return self::success(
			array(
				'query'    => array(
					'page'      => $page,
					'page_size' => $page_size,
				),
				'elements' => $elements,
			)
		);
	}

	/**
	 * Private API key
	 *
	 * @var string
	 */
	private string $private_api_key;

	/**
	 * Sandbox mode
	 *
	 * @var mixed
	 */
	private mixed $sandbox;

	/**
	 * Construct
	 *
	 * @param string $private_api_key .
	 * @param mixed  $sandbox .
	 * @throws InvalidArgumentException .
	 */
	public function __construct( string $private_api_key, mixed $sandbox ) {
		if ( empty( $private_api_key ) ) {
			throw new InvalidArgumentException( 'Aplazame Private API Key is required' );
		}

		$this->private_api_key = $private_api_key;
		$this->sandbox         = $sandbox;
	}

	/**
	 * Process
	 *
	 * @param string     $path .
	 * @param array      $query_arguments .
	 * @param array|null $payload .
	 *
	 * @return void
	 */
	public function process( string $path, array $query_arguments, ?array $payload ): void {
		$response = $this->route( $path, $query_arguments, $payload );

		status_header( $response['status_code'] );

		wp_send_json( $response['payload'] );
	}

	/**
	 * Route
	 *
	 * @param string     $path .
	 * @param array      $query_arguments .
	 * @param array|null $payload .
	 *
	 * @return array
	 */
	public function route( string $path, array $query_arguments, ?array $payload ): array {
		if ( ! $this->verify_authentication() ) {
			return self::forbidden();
		}

		switch ( $path ) {
			case '/confirm/':
				include_once 'class-aplazame-api-confirmcontroller.php';
				$controller = new Aplazame_Api_ConfirmController( $this->sandbox );

				return $controller->confirm( $payload );
			case '/order/history/':
				include_once 'class-aplazame-api-ordercontroller.php';
				$controller = new Aplazame_Api_OrderController();

				return $controller->history( $query_arguments );
			default:
				return self::not_found();
		}
	}

	/**
	 * Authentication
	 *
	 * @return bool
	 */
	private function verify_authentication(): bool {
		$private_key = $this->private_api_key;

		$authorization = $this->get_authorization_from_request();
		if ( ! $authorization || empty( $private_key ) ) {
			return false;
		}

		return ( $authorization === $private_key );
	}

	/**
	 * Auth from req
	 *
	 * @return string|bool
	 */
	private function get_authorization_from_request(): string|bool {
		$token = isset( $_GET['access_token'] ) ? sanitize_key( stripslashes_deep( $_GET['access_token'] ) ) : false;
		if ( $token ) {
			return $token;
		}

		return false;
	}
}
