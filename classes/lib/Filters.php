<?php

class Aplazame_Filters
{
	/**
	 * @param int $amount
	 *
	 * @return int
	 */
	public static function decimals( $amount = 0 ) {
		$ret = '';
		$str = sprintf( '%.2f', $amount );

		if ( strcmp( $str[0], '-' ) === 0 ) {
			$str = substr( $str, 1 );
			$ret = '-';
		}

		$parts = explode( '.', $str, 2 );

		if ( ($parts === false) || (empty( $parts )) ||
				(strcmp( $parts[0], 0 ) === 0 && strcmp( $parts[1], '00' ) === 0) ) {
			return 0;
		}

		$ret .= ltrim( $parts[0] . substr( $parts[1], 0, 2 ), '0' );
		return intval( $ret );
	}
}
