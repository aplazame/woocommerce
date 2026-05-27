<?php

/**
 * Decimal Type.
 */
class Aplazame_Sdk_Serializer_Decimal implements Aplazame_Sdk_Serializer_JsonSerializable {

	/**
	 * From float
	 *
	 * @param mixed $value .
	 *
	 * @return self
	 */
	public static function fromFloat( mixed $value ): Aplazame_Sdk_Serializer_Decimal {
		return new self( (int) number_format( $value, 2, '', '' ) );
	}

	/**
	 * Value
	 *
	 * @var null|int
	 */
	public ?int $value;

	/**
	 * Construct
	 *
	 * @param int $value .
	 */
	public function __construct( int $value ) {
		$this->value = $value;
	}

	/**
	 * As float
	 *
	 * @return float|int
	 */
	public function asFloat(): float|int {
		return $this->value / 100;
	}

	/**
	 * JSON serialize
	 *
	 * @return int|null
	 */
	public function json_serialize(): ?int {
		return $this->value;
	}
}
