<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Elem_Anon_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class Elem_Anon_Tokens {

	/**
	 *
	 */
	public function __construct() {

		add_filter( 'automator_maybe_parse_token', array( $this, 'elem_token' ), 20, 6 );
	}

	/**
	 * Parse the token.
	 *
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return null|string
	 */
	public function elem_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {

		$piece = 'ELEMFORM';
		if ( $pieces ) {
			if ( 'ANONELEMSUBMITFIELD' === $pieces[1] || 'ANONELEMSUBMITFORM' === $pieces[1] || 'ELEMSUBMITFIELD' === $pieces[1] ) {
				if ( key_exists( $pieces[2], $trigger_data[0]['meta'] ) ) {
					if ( isset( $trigger_data[0]['meta'][ $pieces[2] . '_readable' ] ) ) {
						$value = $trigger_data[0]['meta'][ $pieces[2] . '_readable' ];
					} else {
						$value = $trigger_data[0]['meta'][ $pieces[2] ];
					}
				}
			}
		}

		return $value;
	}
}
