<?php

/**
 * This class assist to compose API models serializable as JSON for PHP versions prior to v5.4.
 */
class Aplazame_Sdk_Serializer_JsonSerializer {

	/**
	 * Important: This method does not return a JSON string, the return of this method must be encoded with `json_encode()`.
	 *
	 * @param mixed $value .
	 *
	 * @return mixed a value valid for to be used with native `json_encode()` function
	 * @throws DomainException .
	 */
	public static function serialize_value( mixed $value ): mixed {
		if ( $value instanceof Aplazame_Sdk_Serializer_JsonSerializable || $value instanceof \JsonSerializable ) {
			return $value->json_serialize();
		}

		if ( is_object( $value ) ) {
			foreach ( get_object_vars( $value ) as $nested_key => $nested_value ) {
				$value->{$nested_key} = self::serialize_value( $nested_value );
			}

			return $value;
		}

		if ( is_array( $value ) ) {
			foreach ( $value as &$nested_value ) {
				$nested_value = self::serialize_value( $nested_value );
			}

			return $value;
		}

		if ( $value instanceof DateTime || $value instanceof \DateTimeInterface ) {
			throw new DomainException( 'Please wrap your DateTime objects with Aplazame_Sdk_Serializer_Date::fromDateTime' );
		}

		if ( is_float( $value ) ) {
			throw new DomainException( 'Please wrap your float values with Aplazame_Sdk_Serializer_Decimal::fromFloat' );
		}

		return $value;
	}
}
