<?php

/**
 * Exception thrown when there is communication possible with the API.
 */
class Aplazame_Sdk_Api_ApiCommunicationException extends RuntimeException implements Aplazame_Sdk_Api_AplazameExceptionInterface {

	/**
	 * @param Exception $exception
	 *
	 * @return Aplazame_Sdk_Api_ApiCommunicationException
	 */
	public static function fromException( Exception $exception ) {
		return new self( $exception->getMessage(), $exception->getCode(), $exception );
	}
}
