<?php

/**
 * Customer.
 */
class Aplazame_Aplazame_BusinessModel_Customer {

	public static function createFromOrder( WC_Order $order ) {
		$customer = $order->get_user();
		if ( $customer ) {
			return self::createFromUser( $customer );
		}

		return self::createGuessCustomerFromOrder( $order );
	}

	public static function createFromUser( WP_User $user ) {
		switch ( $user->getGender() ) {
			case '1':
				$gender = 1;
				break;
			case '2':
				$gender = 2;
				break;
			default:
				$gender = 0;
		}

		$aCustomer = new self();
		$aCustomer->email = $user->user_email;
		$aCustomer->type = 'e';
		$aCustomer->gender = $gender;
		$aCustomer->id = $user->ID;
		$aCustomer->first_name = $user->first_name;
		$aCustomer->last_name = $user->last_name;
		if ( ($birthday = $user->getDob()) !== null ) {
			$aCustomer->birthday = Aplazame_Sdk_Serializer_Date::fromDateTime( new DateTime( $birthday ) );
		}
		if ( ($document_id = $user->getTaxvat()) !== null ) {
			$aCustomer->document_id = $document_id;
		}
		$aCustomer->date_joined = Aplazame_Sdk_Serializer_Date::fromDateTime( new DateTime( $user->user_registered ) );

		return $aCustomer;
	}

	public static function createGuessCustomerFromOrder( WC_Order $order ) {
		$aCustomer = new self();
		if (method_exists($order, 'get_id')) {
			$aCustomer->email = $order->get_billing_email();
		} else {
			$aCustomer->email = $order->billing_email;
		}
		$aCustomer->type = 'g';
		$aCustomer->gender = 0;

		return $aCustomer;
	}
}
