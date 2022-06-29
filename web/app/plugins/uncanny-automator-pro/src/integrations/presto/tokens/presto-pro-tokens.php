<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Presto_Tokens;

/**
 * Class Presto_Pro_Tokens
 * @package Uncanny_Automator_Pro
 */
class Presto_Pro_Tokens extends Presto_Tokens {

	public function __construct() {

		add_filter( 'automator_maybe_parse_token', array( $this, 'percent' ), 20, 6 );

	}

	/**
	 * Parse the percent token.
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
	public function percent( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {

		$meta = 'VIDEOPERCENT';

		$valid = $this->valid_token(
			$meta,
			compact( 'value', 'pieces', 'recipe_id', 'trigger_data', 'user_id', 'replace_args' )
		);

		if ( ! $valid ) {
			return $value;
		}

		$trigger_meta = $trigger_data[0]['meta'];

		if ( empty( $trigger_meta[ $meta . '_readable' ] ) ) {
			return $value;
		}

		return $trigger_meta[ $meta . '_readable' ];
	}
	
	/**
	 * valid_token
	 *
	 * @param  string $meta
	 * @param  array $token
	 * @return bool
	 */
	public function valid_token( $meta, $token ) {

		extract( $token );

		if ( ! $pieces ) {
			return;
		}

		if ( empty( $pieces[2] ) ) {
			return;
		}

		if ( $meta !== $pieces[2] ) {
			return;
		}

		if ( empty( $trigger_data[0] ) ) {
			return;
		}

		if ( empty( $trigger_data[0]['meta'] ) ) {
			return;
		}

		return true;

	}

}
