<?php

namespace Uncanny_Automator_Pro;

/**
 * Class ET_ANON_TOKENS
 * @package Uncanny_Automator_Pro
 */
class ET_ANON_TOKENS {

	/**
	 * ET_ANON_TOKENS constructor.
	 */
	public function __construct() {
		add_filter( 'automator_maybe_trigger_ec_attendeeregistered_tokens', [ $this, 'et_anonattendee_possible_tokens' ], 20, 2 );
		add_filter( 'automator_maybe_parse_token', [ $this, 'et_anonattendee_tokens' ], 20, 6 );
	}

	public function et_anonattendee_possible_tokens( $tokens = array(), $args = array() ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}
		$trigger_integration = $args['integration'];
		$trigger_meta        = $args['meta'];

		$fields = array(
			array(
				'tokenId'         => 'holder_name',
				'tokenName'       => __( 'Attendee name', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
			array(
				'tokenId'         => 'holder_email',
				'tokenName'       => __( 'Attendee email', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			)
		);

		$tokens = array_merge( $tokens, $fields );

		return $tokens;
	}

	public function et_anonattendee_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( $pieces ) {
			if ( in_array( 'holder_name', $pieces ) || in_array( 'holder_email', $pieces ) ) {
				global $wpdb;
				$trigger_id     = $pieces[0];
				$trigger_meta   = $pieces[2];
				$trigger_log_id = isset( $replace_args['trigger_log_id'] ) ? absint( $replace_args['trigger_log_id'] ) : 0;
				$entry          = $wpdb->get_var( "SELECT meta_value
													FROM {$wpdb->prefix}uap_trigger_log_meta
													WHERE meta_key = '{$trigger_meta}'
													AND automator_trigger_log_id = {$trigger_log_id}
													AND automator_trigger_id = {$trigger_id}
													LIMIT 0,1" );

				$value = maybe_unserialize( $entry );
			}
		}

		return $value;
	}

}
