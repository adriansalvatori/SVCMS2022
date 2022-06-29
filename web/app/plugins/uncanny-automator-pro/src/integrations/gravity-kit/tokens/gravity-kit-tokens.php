<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Gf_Tokens;

/**
 * Class Gravity_Kit_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class Gravity_Kit_Tokens extends Gf_Tokens {
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'automator_before_trigger_completed', array( $this, 'save_token_data' ), 20, 2 );
		add_filter( 'automator_maybe_trigger_gk_tokens', array( $this, 'gf_entry_possible_tokens' ), 20, 2 );
		add_filter( 'automator_maybe_parse_token', array( $this, 'parse_tokens' ), 20, 6 );
		add_filter(
			'automator_maybe_trigger_gk_gk_entry_metadata_tokens',
			array(
				$this,
				'gf_possible_tokens',
			),
			20,
			2
		);
	}

	/**
	 * save_token_data
	 *
	 * @param mixed $args
	 * @param mixed $trigger
	 *
	 * @return void
	 */
	public function save_token_data( $args, $trigger ) {
		if ( ! isset( $args['trigger_args'] ) || ! isset( $args['entry_args']['code'] ) ) {
			return;
		}

		$trigger_code = $args['entry_args']['code'];

		if ( 'GK_ENTRY_APPROVED' === $trigger_code || 'GK_ENTRY_DISAPPROVED' === $trigger_code ) {
			$gk_entry_id       = array_shift( $args['trigger_args'] );
			$trigger_log_entry = $args['trigger_entry'];
			if ( ! empty( $gk_entry_id ) ) {
				Automator()->db->token->save( 'GFENTRYID', $gk_entry_id, $trigger_log_entry );
			}
		}

	}

	/**
	 * parse_tokens
	 *
	 * @param mixed $value
	 * @param mixed $pieces
	 * @param mixed $recipe_id
	 * @param mixed $trigger_data
	 * @param mixed $user_id
	 * @param mixed $replace_args
	 *
	 * @return void
	 */
	public function parse_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {

		if ( ! is_array( $pieces ) || ! isset( $pieces[1] ) || ! isset( $pieces[2] ) ) {
			return $value;
		}

		$codes    = array( 'GK_ENTRY_APPROVED', 'GK_ENTRY_DISAPPROVED', 'GFENTRYTOKENS', 'GK_ENTRY_METADATA' );
		$to_match = $pieces[1];

		if ( ! in_array( $to_match, $codes, true ) ) {
			return $value;
		}

		if ( isset( $pieces[2] ) && 'GK_ENTRY_METADATA_ID' === $pieces[2] ) {
			$t_data = array_shift( $trigger_data );

			return $t_data['meta']['GK_ENTRY_METADATA'];
		}

		if ( isset( $pieces[2] ) && 'GK_ENTRY_METADATA' === $pieces[2] ) {
			$t_data = array_shift( $trigger_data );

			return $t_data['meta']['GK_ENTRY_METADATA_readable'];
		}

		if ( 'GFENTRYTOKENS' === $pieces[1] ) {
			$entry_id = Automator()->db->token->get( 'GFENTRYID', $replace_args );
			if ( 'GFENTRYID' === $pieces[2] ) {
				$value = $entry_id;
			} else {
				global $wpdb;
				$entry = $wpdb->get_row( $wpdb->prepare( "SELECT * from {$wpdb->prefix}gf_entry WHERE id=%d", $entry_id ), ARRAY_A );
				if ( 'GFUSERIP' === $pieces[2] ) {
					$value = $entry['ip'];
				} elseif ( 'GFENTRYSOURCEURL' === $pieces[2] ) {
					$value = $entry['source_url'];
				} elseif ( 'GFENTRYDATE' === $pieces[2] ) {
					$value = \GFCommon::format_date( $entry['date_created'], false, 'Y/m/d' );
				}
			}
		}

		if ( 'GK_ENTRY_METADATA' === $pieces[1] ) {
			global $wpdb;
			$entry_id    = Automator()->db->token->get( 'GFENTRYID', $replace_args );
			$token_piece = $pieces[2];
			$token_info  = explode( '|', $token_piece );
			$form_id     = $token_info[0];
			$meta_key    = isset( $token_info[1] ) ? $token_info[1] : '';
			$meta_value  = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value from {$wpdb->prefix}gf_entry_meta WHERE entry_id=%d AND form_id=%d AND meta_key=%s", $entry_id, $form_id, $meta_key ) );
			$value       = ! empty( $meta_value ) ? $meta_value : '';
		}

		return $value;

	}
}
