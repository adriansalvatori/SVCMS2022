<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Gp_Pro_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class Gp_Pro_Tokens {

	public function __construct() {
		add_filter( 'automator_maybe_trigger_gp_gptotalpoints_tokens', array( $this, 'gp_possible_tokens' ), 20, 2 );
		add_filter( 'automator_maybe_parse_token', array( $this, 'gamipress_parse_token' ), 20, 6 );
	}

	public function gp_possible_tokens( $tokens = array(), $args = array() ) {
		$trigger_meta = $args['meta'];

		$fields = array(
			array(
				'tokenId'         => 'GPPERVIOUSPOINTS',
				'tokenName'       => __( 'Previous point total', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
			array(
				'tokenId'         => 'GPNEWPOINTS',
				'tokenName'       => __( 'New point total', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
		);
		$tokens = array_merge( $tokens, $fields );

		return $tokens;
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
	public function gamipress_parse_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( in_array( 'GPTOTALPOINTS', $pieces, true ) ) {
			if ( $trigger_data ) {
				foreach ( $trigger_data as $trigger ) {
					$trigger_id     = $trigger['ID'];
					$trigger_log_id = $replace_args['trigger_log_id'];
					$meta_key       = $pieces[2];
					$meta_value     = Automator()->helpers->recipe->get_form_data_from_trigger_meta( $meta_key, $trigger_id, $trigger_log_id, $user_id );
					if ( ! empty( $meta_value ) ) {
						$value = maybe_unserialize( $meta_value );
					}
				}
			}
		}

		return $value;

	}

}
