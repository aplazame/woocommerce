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
		$aAddress->first_name = $order->{$type . '_' . 'first_name'};
		$aAddress->last_name = $order->{$type . '_' . 'last_name'};
		$aAddress->street = $order->{$type . '_' . 'address_1'};
		$aAddress->city = $order->{$type . '_' . 'city'};
		$aAddress->state = $order->{$type . '_' . 'state'};
		$aAddress->country = $order->{$type . '_' . 'country'};
		$aAddress->postcode = $order->{$type . '_' . 'postcode'};
		$aAddress->phone = $order->{$type . '_' . 'phone'};
		$aAddress->address_addition = $order->{$type . '_' . 'address_2'};

		return $aAddress;
	}
}
