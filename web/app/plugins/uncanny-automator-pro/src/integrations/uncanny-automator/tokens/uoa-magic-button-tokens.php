<?php
/**
 * Magic button related tokens class.
 *
 */

namespace Uncanny_Automator_Pro;

/**
 * Class UOA_Magic_Button_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class UOA_Magic_Button_Tokens {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'UOA';

	public function __construct() {

		add_filter( 'automator_maybe_trigger_uoa_wpmagicbutton_tokens', [ $this, 'wp_possible_tokens' ], 20, 2 );
		add_filter( 'automator_maybe_trigger_uoa_wpmagiclink_tokens', [ $this, 'wp_possible_tokens' ], 20, 2 );
		add_filter( 'automator_maybe_parse_token', [ $this, 'parse_magicbutton_token' ], 20, 6 );
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
	public function parse_magicbutton_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		$piece      = 'WPMAGICBUTTON';
		$piece_link = 'WPMAGICLINK';

		if ( $pieces ) {
			if ( in_array( $piece, $pieces ) || in_array( $piece_link, $pieces ) ) {
				global $uncanny_automator;

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
