<?php

class WC_Aplazame_Proxy {
	/**
	 * @var Aplazame_Client
	 */
	private $client;

	public function __construct( Aplazame_Client $client ) {
		$this->client = $client;
	}

	public function action() {
		if ( ! current_user_can( 'edit_products' ) ) {
			die( - 1 );
		}

		if ( ! isset( $_POST['method'] ) ) {
			die( - 1 );
		}
		$method = $_POST['method'];
		if ( ! in_array( $method, array( 'DELETE', 'GET', 'POST' ), true ) ) {
			die( - 1 );
		}

		if ( ! isset( $_POST['path'] ) ) {
			die( - 1 );
		}
		$path = $_POST['path'];

		if ( ! isset( $_POST['data'] ) ) {
			$data = null;
		} else {
			$data = json_decode( stripslashes_deep( $_POST['data'] ), true );
		}

		$response = $this->client->apiClient->request( $method, $path, $data );

		wp_send_json( $response );
	}
}

/** @var WC_Aplazame $aplazame */
global $aplazame;

$proxy = new WC_Aplazame_Proxy( $aplazame->get_client() );

add_action( 'wp_ajax_aplazame-proxy', array( $proxy, 'action' ) );
