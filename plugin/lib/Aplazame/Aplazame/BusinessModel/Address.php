<?php

/** Model Address class */
class Aplazame_Aplazame_BusinessModel_Address {
	/**
	 * Create from order
	 *
	 * @param WC_Order $order .
	 * @param string   $type .
	 *
	 * @return self
	 */
	public static function create_from_order( WC_Order $order, string $type ): Aplazame_Aplazame_BusinessModel_Address {
		$a_address = new self();
		foreach ( array(
			'first_name'       => 'first_name',
			'last_name'        => 'last_name',
			'street'           => 'address_1',
			'city'             => 'city',
			'state'            => 'state',
			'country'          => 'country',
			'postcode'         => 'postcode',
			'phone'            => 'phone',
			'address_addition' => 'address_2',
		) as $key => $field ) {
			$field           = $type . '_' . $field;
			$a_address->$key = WC_Aplazame::method_or_attribute( $order, 'get_' . $field, $field );
		}

		return $a_address;
	}
}
