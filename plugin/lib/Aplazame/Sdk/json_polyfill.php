<?php

/*
 * json_last_error_msg is available since PHP 5.5.0
 */
if ( ! function_exists( 'json_last_error_msg' ) ) {
	function json_last_error_msg() {
		static $ERRORS = array(
			JSON_ERROR_NONE => 'No error',
			JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
			JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
			JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
			JSON_ERROR_SYNTAX => 'Syntax error',
			JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
		);

		$error = json_last_error();

		return isset( $ERRORS[ $error ] ) ? $ERRORS[ $error ] : 'Unknown error';
	}
}
