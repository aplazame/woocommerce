<?php

interface Aplazame_Sdk_Http_ClientInterface {

	/**
	 * @param Aplazame_Sdk_Http_RequestInterface $request
	 *
	 * @return Aplazame_Sdk_Http_ResponseInterface
	 *
	 * @throws RuntimeException If requests cannot be performed due network issues.
	 */
	public function send( Aplazame_Sdk_Http_RequestInterface $request);
}
