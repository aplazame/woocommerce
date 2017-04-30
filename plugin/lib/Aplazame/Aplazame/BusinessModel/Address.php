<?php

class Aplazame_Aplazame_BusinessModel_Address {

	/**
	 * @param WC_Order $order
	 * @param string   $type
	 *
	 * @return self
	 */
	public static function createFromOrder( WC_Order $order, $type ) {
		$aAddress = new self();
		foreach ( array(
			'first_name' => 'first_name',
			'last_name' => 'last_name',
			'street' => 'address_1',
			'city' => 'city',
			'state' => 'state',
			'country' => 'country',
			'postcode' => 'postcode',
			'phone' => 'phone',
			'address_addition' => 'address_2',
		) as $key => $field ) {
			$field = $type . '_' . $field;

			if (method_exists($order, 'get_' . $field)) {
				$aAddress->$key = $order->{'get_' . $field}();
			} else {
				$aAddress->$key = $order->$field;
			}
		}

		return $aAddress;
	}
}
