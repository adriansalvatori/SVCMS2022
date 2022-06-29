<?php

namespace Uncanny_Automator_Pro;

use FrmField;
use FrmForm;

/**
 * Class Fi_Anon_Tokens
 * @package Uncanny_Automator_Pro
 */
class Fi_Anon_Tokens {
	
	/**
	 * Fi_Anon_Tokens constructor.
	 */
	public function __construct() {
		add_filter( 'automator_maybe_parse_token', [ $this, 'fi_token' ], 20, 6 );
	}

	/**
	 * Parse the token.
	 *
	 * @param string $value .
	 * @param array $pieces .
	 * @param string $recipe_id .
	 *
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return null|string
	 */
	public function fi_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( $pieces ) {
			if ( in_array( 'ANONFIFORM', $pieces, true ) || in_array( 'ANONFISUBMITFIELD', $pieces, true ) || in_array( 'FIUPDATEFIELD', $pieces, true ) || in_array( 'FISUBMITFORM', $pieces, true ) ) {
				global $wpdb;
				$trigger_id   = $pieces[0];
				$trigger_meta = $pieces[1];
				$field        = $pieces[2];
				if ( $pieces[2] === 'ANONFIFORM' ) {
					if ( isset( $trigger_data[0]['meta']['ANONFIFORM_readable'] ) ) {
						$value = $trigger_data[0]['meta']['ANONFIFORM_readable'];
					}
				} elseif ( $pieces[2] === 'ANONFIFORM_ID' ) {
					if ( isset( $trigger_data[0]['meta']['ANONFIFORM'] ) ) {
						$value = $trigger_data[0]['meta']['ANONFIFORM'];
					}
				} elseif ( $pieces[2] === 'ANONFISUBMITFIELD' ) {
					if ( isset( $trigger_data[0]['meta']['ANONFISUBMITFIELD_readable'] ) ) {
						$value = $trigger_data[0]['meta']['ANONFISUBMITFIELD_readable'];
					}
				} elseif ( $pieces[2] === 'SUBVALUE' ) {
					if ( isset( $trigger_data[0]['meta']['SUBVALUE'] ) ) {
						$value = $trigger_data[0]['meta']['SUBVALUE'];
					}
				} elseif ( $pieces[2] === 'FIFORM' ) {
					if ( isset( $trigger_data[0]['meta']['FIFORM_readable'] ) ) {
						$value = $trigger_data[0]['meta']['FIFORM_readable'];
					}
				} else {
					$trigger_log_id = isset( $replace_args['trigger_log_id'] ) ? absint( $replace_args['trigger_log_id'] ) : 0;
					$entry          = $wpdb->get_var( "SELECT meta_value
													FROM {$wpdb->prefix}uap_trigger_log_meta
													WHERE meta_key = '$trigger_meta'
													AND automator_trigger_log_id = $trigger_log_id
													AND automator_trigger_id = $trigger_id
													LIMIT 0, 1" );
					$entry          = maybe_unserialize( $entry );
					$to_match       = "{$trigger_id}:{$trigger_meta}:{$field}";
					if ( is_array( $entry ) && key_exists( $to_match, $entry ) ) {
						$value = $entry[ $to_match ];
					}
				}
			}
		}

		return $value;
	}
}
