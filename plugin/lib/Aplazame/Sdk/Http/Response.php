<?php

/** SDK HTTP response */
class Aplazame_Sdk_Http_Response implements Aplazame_Sdk_Http_ResponseInterface {
	/**
	 * Phrases
	 *
	 * @var array Map of standard HTTP status code/reason phrases
	 */
	private static array $phrases = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-status',
		208 => 'Already Reported',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => 'Switch Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Large',
		415 => 'Unsupported Media Type',
		416 => 'Requested range not satisfiable',
		417 => 'Expectation Failed',
		418 => "'I'm a teapot'",
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Unordered Collection',
		426 => 'Upgrade Required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		451 => 'Unavailable For Legal Reasons',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Time-out',
		505 => 'HTTP Version not supported',
		506 => 'Variant Also Negotiates',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		511 => 'Network Authentication Required',
	);

	/**
	 * Status code
	 *
	 * @var int
	 */
	private int $status_code;

	/**
	 * Reason phrase
	 *
	 * @var string
	 */
	private mixed $reason_phrase = '';

	/**
	 * Body
	 *
	 * @var mixed
	 */
	private mixed $body;

	/**
	 * Construct
	 *
	 * @param int   $status_code .
	 * @param mixed $body .
	 */
	public function __construct( int $status_code, mixed $body ) {
		$this->status_code = $status_code;
		$this->body        = $body;

		if ( isset( self::$phrases[ $this->status_code ] ) ) {
			$this->reason_phrase = self::$phrases[ $status_code ];
		} else {
			$this->reason_phrase = '';
		}
	}

	/**
	 * Get status code
	 *
	 * @return int
	 */
	public function get_status_code(): int {
		return $this->status_code;
	}

	/**
	 * Get reason phrase
	 *
	 * @return string
	 */
	public function get_reason_phrase(): string {
		return $this->reason_phrase;
	}

	/**
	 * Get body
	 *
	 * @return mixed
	 */
	public function get_body(): mixed {
		return $this->body;
	}
}
