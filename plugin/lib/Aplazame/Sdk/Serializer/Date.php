<?php

/**
 * DateTime Type.
 */
class Aplazame_Sdk_Serializer_Date implements Aplazame_Sdk_Serializer_JsonSerializable {

	/**
	 * @param DateTime $value
	 *
	 * @return Aplazame_Sdk_Serializer_Date
	 */
	public static function fromDateTime( $value ) {
		return new self( $value->format( DateTime::ISO8601 ) );
	}

	/**
	 * @var null|string
	 */
	public $value;

	/**
	 * @param string $value
	 */
	public function __construct( $value ) {
		$this->value = $value;
	}

	/**
	 * @return DateTime
	 */
	public function asDateTime() {
		$dateTime = DateTime::createFromFormat( DateTime::ISO8601, $this->value );

		return $dateTime;
	}

	public function jsonSerialize() {
		return $this->value;
	}
}
