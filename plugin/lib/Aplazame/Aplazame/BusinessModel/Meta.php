<?php

/**
 * Meta.
 */
class Aplazame_Aplazame_BusinessModel_Meta {

	public static function create() {
		$aMeta          = new self();
		$aMeta->module  = array(
			'name'    => 'aplazame:woocommerce',
			'version' => WC_Aplazame::VERSION,
		);
		$aMeta->version = WC()->version;

		return $aMeta;
	}
}
