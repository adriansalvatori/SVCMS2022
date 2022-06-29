<?php

namespace Uncanny_Automator_Pro;


use Uncanny_Automator\Wp_Tokens;

/**
 * Class UOA_Anon_Tokens
 * @package Uncanny_Automator_Pro
 */
class UOA_Anon_Tokens extends Wp_Tokens {


	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'UOA';

	/**
	 * WP_Anon_Tokens constructor.
	 */
	public function __construct() {
		add_filter( 'automator_maybe_trigger_uoa_anonwpmagicbutton_tokens', [ $this, 'wp_possible_tokens' ], 20, 2 );
		add_filter( 'automator_maybe_trigger_uoa_anonwpmagiclink_tokens', [ $this, 'wp_possible_tokens' ], 20, 2 );
		add_filter( 'automator_maybe_parse_token', [ $this, 'parse_anon_token' ], 20, 6 );
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function wp_possible_tokens( $tokens = [], $args = [] ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}
		$trigger_integration = $args['integration'];
		$trigger_meta        = $args['meta'];

		$fields = [
			[
				'tokenId'         => 'user_id',
				'tokenName'       => __( 'User ID', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			],
			[
				'tokenId'         => 'username',
				'tokenName'       => __( 'Username', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			],
			[
				'tokenId'         => 'email',
				'tokenName'       => __( 'User email', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			],
			[
				'tokenId'         => 'automator_button_post_id',
				'tokenName'       => __( 'Post ID', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			],
			[
				'tokenId'         => 'automator_button_post_title',
				'tokenName'       => __( 'Post title', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			],
			[
				'tokenId'         => 'automator_button_post_url',
				'tokenName'       => __( 'Post URL', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			],
		];

		$tokens = array_merge( $tokens, $fields );

		return $tokens;
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 *
	 * @return mixed
	 */
	public function parse_anon_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		$piece      = 'ANONWPMAGICBUTTON';
		$piece_link = 'ANONWPMAGICLINK';
		if ( $pieces ) {
			if ( in_array( $piece, $pieces ) || in_array( $piece_link, $pieces ) ) {
				if ( $trigger_data ) {
					foreach ( $trigger_data as $trigger ) {
						$trigger_id = $trigger['ID'];
						$meta_field = $pieces[2];
						$meta_value = $this->get_form_data_from_trigger_meta( $meta_field, $trigger_id );
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


	/**
	 * @param $meta_key
	 * @param $trigger_id
	 *
	 * @return mixed|string
	 */
	public function get_form_data_from_trigger_meta( $meta_key, $trigger_id ) {
		global $wpdb;
		if ( empty( $meta_key ) || empty( $trigger_id ) ) {
			return '';
		}

		$meta_value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}uap_trigger_log_meta WHERE meta_key = %s AND automator_trigger_id = %d ORDER BY ID DESC LIMIT 0,1", $meta_key, $trigger_id ) );
		if ( ! empty( $meta_value ) ) {
			return maybe_unserialize( $meta_value );
		}

		return '';
	}
}
