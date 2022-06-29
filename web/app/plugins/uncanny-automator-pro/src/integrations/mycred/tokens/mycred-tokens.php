<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Mycred_Tokens
 * @package Uncanny_Automator_Pro
 */
class Mycred_Tokens {
	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'MYCRED';

	/**
	 * Uc_Tokens constructor.
	 */
	public function __construct() {
		add_filter( 'automator_maybe_parse_token', [ $this, 'parse_mycred_token' ], 20, 6 );
	}

	/**
	 * Only load this integration and its triggers and actions if the related plugin is active
	 *
	 * @param $status
	 * @param $code
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $code ) {

		if ( self::$integration === $code ) {

			$status = true;
		}

		return $status;
	}


	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return mixed|string
	 */
	public function parse_mycred_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		$tokens = [
			'MYCREDPOINTTYPE',
			'CURRENTBALANCE',
			'TOTALBALANCE',
			'TOTALPOINTTYPE',
			'MYCREDPOINTSTYPES',
			'EARNSRANK'
		];

		if ( $pieces && isset( $pieces[2] ) ) {
			$meta_field = $pieces[2];
			if ( ! empty( $meta_field ) && in_array( $meta_field, $tokens ) ) {
				if ( $trigger_data ) {
					foreach ( $trigger_data as $trigger ) {
						if ( 'MYCREDPOINTTYPE' == $meta_field || 'TOTALPOINTTYPE' == $meta_field ||
						     'MYCREDPOINTSTYPES' == $meta_field || 'EARNSRANK' == $meta_field ) {
							$value = isset( $trigger['meta'][ $meta_field . '_readable' ] ) ? $trigger['meta'][ $meta_field . '_readable' ] : '';
						} else {
							$value = isset( $trigger['meta'][ $meta_field ] ) ? $trigger['meta'][ $meta_field ] : '';
						}
					}
				}
			}
		}

		return $value;
	}
}