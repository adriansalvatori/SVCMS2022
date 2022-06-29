<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Groundhogg_Pro_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class Groundhogg_Pro_Tokens {
	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'GH';

	public function __construct() {
		add_filter( 'automator_maybe_parse_token', array( $this, 'gh_parse_tokens' ), 20, 6 );
	}

	public function gh_parse_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( $pieces ) {
			if ( in_array( 'GHTAGAPPLIED', $pieces, true ) || in_array( 'GHTAGREMOVED', $pieces, true ) ) {
				if ( $trigger_data ) {
					foreach ( $trigger_data as $trigger ) {
						$token_args = array(
							'trigger_id'     => $trigger['ID'],
							'trigger_log_id' => $replace_args['trigger_log_id'],
							'user_id'        => $user_id,
						);
						$meta_value = Automator()->db->trigger->get_token_meta( $pieces[2], $token_args );
						if ( ! empty( $meta_value ) ) {
							$value = maybe_unserialize( $meta_value );
						}
					}
				}
			}
		}

		return $value;
	}
}
