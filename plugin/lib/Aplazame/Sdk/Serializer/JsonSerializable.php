<?php

interface Aplazame_Sdk_Serializer_JsonSerializable {

	/**
	 * Serializes the object to a value that can be serialized natively by json_encode().
	 *
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 *
	 * @return mixed data which can be serialized by json_encode(), which is a value of any type other than a resource.
	 */
	public function jsonSerialize();
}
