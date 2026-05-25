<?php

/** Model Meta class*/
class Aplazame_Aplazame_BusinessModel_Meta {
	/**
	 * Create
	 *
	 * @return self
	 */
	public static function create(): Aplazame_Aplazame_BusinessModel_Meta {
		$a_meta          = new self();
		$a_meta->module  = array(
			'name'    => 'aplazame:woocommerce',
			'version' => WC_Aplazame::VERSION,
		);
		$a_meta->version = WC()->version;

		return $a_meta;
	}
}
