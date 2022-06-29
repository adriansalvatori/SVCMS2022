<?php

namespace Uncanny_Automator_Pro;


use Uncanny_Automator\Fr_Tokens;

/**
 * Class Fr_Anon_Tokens
 * @package Uncanny_Automator_Pro
 */
class Fr_Anon_Tokens extends Fr_Tokens {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'FR';

	/**
	 * Fr_Anon_Tokens constructor.
	 */
	public function __construct() {
		add_filter( 'automator_maybe_parse_token', [ $this, 'fr_token_anon' ], 55, 6 );
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
	public function fr_token_anon( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		$piece = 'FRFORM';
		if ( ! $pieces ) {
			return $value;
		}
		$recipe_log_id = isset( $replace_args['recipe_log_id'] ) ? (int) $replace_args['recipe_log_id'] : Automator()->maybe_create_recipe_log_entry( $recipe_id, $user_id )['recipe_log_id'];
		if ( ! $trigger_data || ! $recipe_log_id ) {
			return $value;
		}

		foreach ( $trigger_data as $trigger ) {
			if ( empty( $trigger ) ) {
				continue;
			}

			if ( ! is_array( $trigger ) ) {
				continue;
			}

			if ( ! array_key_exists( $piece, $trigger['meta'] ) ) {
				continue;
			}

			// Render Form Name
			if ( isset( $pieces[2] ) && $piece === $pieces[2] ) {
				foreach ( $trigger_data as $t_d ) {
					if ( empty( $t_d ) ) {
						continue;
					}
					if ( isset( $t_d['meta'][ $piece . '_readable' ] ) ) {
						return $t_d['meta'][ $piece . '_readable' ];
					}
				}
			}
			// Render Form ID
			if ( isset( $pieces[2] ) && $piece . '_ID' === $pieces[2] ) {
				foreach ( $trigger_data as $t_d ) {
					if ( empty( $t_d ) ) {
						continue;
					}
					if ( isset( $t_d['meta'][ $piece ] ) ) {
						return $t_d['meta'][ $piece ];
					}
				}
			}

			if ( $pieces[2] === 'FRSUBMITFIELD' ) {
				if ( isset( $trigger_data[0]['meta']['FRSUBMITFIELD_readable'] ) ) {
					return $trigger_data[0]['meta']['FRSUBMITFIELD_readable'];
				}
			}
			if ( $pieces[2] === 'SUBVALUE' ) {
				if ( isset( $trigger_data[0]['meta']['SUBVALUE'] ) ) {
					return $trigger_data[0]['meta']['SUBVALUE'];
				}
			}

			$trigger_id     = $trigger['ID'];
			$trigger_log_id = $replace_args['trigger_log_id'];
			$token_info     = explode( '|', $pieces[2] );
			$form_id        = $token_info[0];
			$meta_key       = $token_info[1];
			$match          = "{$trigger_id}:{$piece}:{$form_id}|{$meta_key}";
			$parse_tokens   = array(
				'trigger_id'     => $trigger_id,
				'trigger_log_id' => $trigger_log_id,
				'user_id'        => $user_id,
			);
			$value          = Automator()->db->trigger->get_token_meta( $match, $parse_tokens );
			$value          = maybe_unserialize( $value );
			if ( is_array( $value ) ) {
				$value = join( '  ', $value );
			}
		}

		return $value;
	}
}
