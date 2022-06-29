<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Bbpress_Pro_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class Bbpress_Pro_Tokens {

	/**
	 * Bbpress_Pro_Tokens constructor.
	 */
	public function __construct() {
		add_filter( 'automator_maybe_parse_token', array( $this, 'parse_bbpress_tokens' ), 20, 6 );
		add_filter( 'automator_maybe_trigger_bb_bbpostareply_tokens', array(
			$this,
			'bb_possible_reply_tokens',
		), 20, 2 );
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function bb_possible_reply_tokens( $tokens = array(), $args = array() ) {
		$trigger_meta = $args['meta'];

		$fields = array(
			array(
				'tokenId'         => 'REPLY_ID',
				'tokenName'       => __( 'Reply ID', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
			array(
				'tokenId'         => 'REPLY_URL',
				'tokenName'       => __( 'Reply URL', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
			array(
				'tokenId'         => 'REPLY_CONTENT',
				'tokenName'       => __( 'Reply content', 'uncanny-automator-pro' ),
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
	 *
	 * @return mixed
	 */
	public function parse_bbpress_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( $pieces ) {
			if ( in_array( 'BBPOSTAREPLY', $pieces ) ) {
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
		}

		return $value;
	}
}
