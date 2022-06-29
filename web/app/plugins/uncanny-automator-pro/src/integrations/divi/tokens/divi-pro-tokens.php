<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Divi_Helpers;

/**
 * Divi Pro tokens
 */
class Divi_Pro_Tokens {

	/**
	 * Divi Pro Token Constructor
	 */
	public function __construct() {
		add_filter( 'automator_maybe_parse_token', array( $this, 'divi_token' ), 20, 6 );
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
	public function divi_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {

		if ( ! in_array( 'DIVIFORM', $pieces, true ) && ! in_array( 'DIVISUBMITFORMFIELD', $pieces, true ) && ! in_array( 'ANONDIVISUBMITFORMFIELD', $pieces, true ) ) {
			return $value;
		}

		if ( empty( $trigger_data ) ) {
			return $value;
		}
		foreach ( $trigger_data as $trigger ) {
			if ( ( 'DIVISUBMITFORMFIELD' === $pieces[1] || 'ANONDIVISUBMITFORMFIELD' === $pieces[1] ) && 'DIVIFORM' === $pieces[2] ) {
				if ( isset( $trigger['meta'][ $pieces[2] . '_readable' ] ) ) {
					return $trigger['meta'][ $pieces[2] . '_readable' ];
				}
			}

			if ( 'DIVISUBMITFORMFIELD' === $pieces[1] && 'DIVISUBMITFORMFIELD' === $pieces[2] ) {
				if ( isset( $trigger['meta'][ $pieces[2] . '_readable' ] ) ) {
					return $trigger['meta'][ $pieces[2] . '_readable' ];
				}
			}
			if ( 'ANONDIVISUBMITFORMFIELD' === $pieces[1] && 'ANONDIVISUBMITFORMFIELD' === $pieces[2] ) {
				if ( isset( $trigger['meta'][ $pieces[2] . '_readable' ] ) ) {
					return $trigger['meta'][ $pieces[2] . '_readable' ];
				}
			}

			if ( ( 'DIVISUBMITFORMFIELD' === $pieces[1] || 'ANONDIVISUBMITFORMFIELD' === $pieces[1] ) && 'SUBVALUE' === $pieces[2] ) {
				if ( isset( $trigger['meta'][ $pieces[2] ] ) ) {
					return $trigger['meta'][ $pieces[2] ];
				}
			}

			$trigger_id     = absint( $trigger['ID'] );
			$trigger_log_id = absint( $replace_args['trigger_log_id'] );
			$parse_tokens   = array(
				'trigger_id'     => $trigger_id,
				'trigger_log_id' => $trigger_log_id,
				'user_id'        => $user_id,
			);

			$meta_key = sprintf( '%d:%s', $pieces[0], $pieces[1] );
			$entry    = Automator()->db->trigger->get_token_meta( $meta_key, $parse_tokens );
			if ( empty( $entry ) ) {
				continue;
			}
			$value = $entry;
			if ( is_array( $value ) ) {
				$value = isset( $entry[ $pieces[2] ] ) ? $entry[ $pieces[2] ] : '';
				if ( is_array( $value ) ) {
					$value = implode( ', ', $value );
				}
			}
		}

		return $value;
	}
}
