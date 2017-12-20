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
			case '/order/{order_id}/history/':
				include_once( 'Aplazame_Api_OrderController.php' );
				$controller = new Aplazame_Api_OrderController();

				return $controller->history( $pathArguments, $queryArguments );
			default:
				return self::not_found();
		}
	}

    /**
     * @return bool
     */
    private function verifyAuthentication()
    {
        $privateKey = $this->private_api_key;

        $authorization = $this->getAuthorizationFromRequest();
        if (!$authorization || empty($privateKey)) {
            return false;
        }

        return ($authorization === $privateKey);
    }

    private function getAuthorizationFromRequest()
    {
    	$token = isset( $_GET['access_token'] ) ? stripslashes_deep( $_GET['access_token'] ) : false;
        if ($token) {
            return $token;
        }

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            $headers = $this->getallheaders();
        }
        $headers = array_change_key_case($headers, CASE_LOWER);

        if (isset($headers['authorization'])) {
            return trim(str_replace('Bearer', '', $headers['authorization']));
        }

        return false;
    }

    private function getallheaders()
    {
        $headers = array();
        $copy_server = array(
            'CONTENT_TYPE'   => 'content-type',
            'CONTENT_LENGTH' => 'content-length',
            'CONTENT_MD5'    => 'content-md5',
        );

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $name = substr($name, 5);
                if (!isset($copy_server[$name]) || !isset($_SERVER[$name])) {
                    $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', $name)))] = $value;
                }
            } elseif (isset($copy_server[$name])) {
                $headers[$copy_server[$name]] = $value;
            }
        }

        if (!isset($headers['authorization'])) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
        }

        return $headers;
    }
}
