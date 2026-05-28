<?php

/** Model Customer class */
class Aplazame_Aplazame_BusinessModel_Customer {
	/**
	 * Create from order
	 *
	 * @param WC_Order $order .
	 *
	 * @return self
	 * @throws DateMalformedStringException .
	 */
	public static function create_from_order( WC_Order $order ): Aplazame_Aplazame_BusinessModel_Customer {
		$customer = $order->get_user();
		if ( $customer ) {
			return self::create_from_user( $customer );
		}

		return self::create_guess_customer_from_order( $order );
	}

	/**
	 * Create from user
	 *
	 * @param WP_User $user .
	 *
	 * @return self
	 * @throws DateMalformedStringException .
	 */
	public static function create_from_user( WP_User $user ): Aplazame_Aplazame_BusinessModel_Customer {
		/** TODO: Repair get gender */
		$gender = match ( $user->getGender() ) {
			'1' => 1,
			'2' => 2,
			default => 0,
		};

		$a_customer             = new self();
		$a_customer->email      = $user->user_email;
		$a_customer->type       = 'e';
		$a_customer->gender     = $gender;
		$a_customer->id         = $user->ID;
		$a_customer->first_name = $user->first_name;
		$a_customer->last_name  = $user->last_name;
		if ( null !== ( $user->getDob() ) ) {
			$a_customer->birthday = Aplazame_Sdk_Serializer_Date::fromDateTime( new DateTime( $user->getDob() ) );
		}
		$a_customer->date_joined = Aplazame_Sdk_Serializer_Date::fromDateTime( new DateTime( $user->user_registered ) );

		return $a_customer;
	}

	/**
	 * Create guess customer from order
	 *
	 * @param WC_Order $order .
	 *
	 * @return self
	 */
	public static function create_guess_customer_from_order( WC_Order $order ): Aplazame_Aplazame_BusinessModel_Customer {
		$a_customer         = new self();
		$a_customer->email  = WC_Aplazame::method_or_attribute( $order, 'get_billing_email', 'billing_email' );
		$a_customer->type   = 'g';
		$a_customer->gender = 0;

		return $a_customer;
	}
}
