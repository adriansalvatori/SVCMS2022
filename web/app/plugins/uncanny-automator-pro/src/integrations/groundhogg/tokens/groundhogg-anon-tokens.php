<?php

namespace Uncanny_Automator_Pro;

/**
 * Class GROUNDHOGG_ANON_TOKENS
 * @package Uncanny_Automator_Pro
 */
class GROUNDHOGG_ANON_TOKENS {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'GH';

	public function __construct() {
		add_filter( 'automator_maybe_trigger_gh_anonghtagapplied_tokens', [
			$this,
			'gh_anon_possible_tokens'
		], 20, 2 );

		add_filter( 'automator_maybe_trigger_gh_anonghtagremoved_tokens', [
			$this,
			'gh_anon_possible_tokens'
		], 20, 2 );

		add_filter( 'automator_maybe_parse_token', [ $this, 'gh_parse_tokens' ], 20, 6 );
	}

	/**
	 * Only load this integration and its triggers and actions if the related plugin is active
	 *
	 * @param $status
	 * @param $plugin
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $plugin ) {
		if ( self::$integration === $plugin ) {
			if ( defined( 'GROUNDHOGG_VERSION' ) ) {
				$status = true;
			} else {
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	function gh_anon_possible_tokens( $tokens = [], $args = [] ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}
		$trigger_meta = $args['meta'];
		$new_tokens   = [
			[
				'tokenId'         => 'CONTACT_EMAIL',
				'tokenName'       => __( 'Contact Email', 'uncanny-automator' ),
				'tokenType'       => 'email',
				'tokenIdentifier' => $trigger_meta,
			],
			[
				'tokenId'         => 'CONTACT_FIRST_NAME',
				'tokenName'       => __( 'Contact First Name', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			],
			[
				'tokenId'         => 'CONTACT_LAST_NAME',
				'tokenName'       => __( 'Contact Last Name', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			],
			[
				'tokenId'         => 'CONTACT_ID',
				'tokenName'       => __( 'Contact ID', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			]
		];
		$tokens       = array_merge( $tokens, $new_tokens );

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
	 * @return string
	 */
	public function gh_parse_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		$piece_one = 'ANONGHTAGAPPLIED';
		$piece_two = 'ANONGHTAGREMOVED';
		global $wpdb;
		if ( $pieces ) {
			if ( in_array( $piece_one, $pieces ) || in_array( $piece_two, $pieces ) ) {
				if ( $trigger_data ) {
					foreach ( $trigger_data as $trigger ) {
						$trigger_id = $trigger['ID'];
						$meta_field = $pieces[2];
						$meta_value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}uap_trigger_log_meta WHERE meta_key = %s AND automator_trigger_id = %d ORDER BY ID DESC LIMIT 0,1", $meta_field, $trigger_id ) );
						if ( ! empty( $meta_value ) ) {
							$meta_value = maybe_unserialize( $meta_value );
						}
						if ( is_array( $meta_value ) ) {
							$value = join( ', ', $meta_value );
						} else {
							$value = $meta_value;
						}
					}
				}
			}
		}

		return $value;
	}

}
