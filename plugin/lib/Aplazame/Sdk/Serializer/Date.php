<?php

/** DateTime Type */
class Aplazame_Sdk_Serializer_Date implements Aplazame_Sdk_Serializer_JsonSerializable {

	/**
	 * From date time
	 *
	 * @param DateTime $value .
	 *
	 * @return Aplazame_Sdk_Serializer_Date
	 */
	public static function fromDateTime( DateTime $value ): Aplazame_Sdk_Serializer_Date {
		return new self( $value->format( DateTime::ISO8601 ) );
	}

	/**
	 * Value
	 *
	 * @var null|string
	 */
	public ?string $value;

	/**
	 * Construct
	 *
	 * @param string $value .
	 */
	public function __construct( string $value ) {
		$this->value = $value;
	}

	/**
	 * As date time
	 *
	 * @return DateTime
	 */
	public function asDateTime(): DateTime {
		return DateTime::createFromFormat( DateTime::ISO8601, $this->value );
	}

	/**
	 * JSON serialize
	 *
	 * @return string|null
	 */
	public function json_serialize(): ?string {
		return $this->value;
	}
}
