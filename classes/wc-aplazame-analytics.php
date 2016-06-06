<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Aplazame_Analytics extends WC_Integration {
	public function __construct() {

		$this->id = 'aplazame_analytics';

		// Settings.
		$this->init_settings();

		// Update options integration
		add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );

		// aplazame-js script
		add_action( 'wp_head', array( $this, 'script' ), 999999 );
	}

	public function script() {

		Aplazame_Helpers::render_to_template( 'layout/header.php' );
	}
}
