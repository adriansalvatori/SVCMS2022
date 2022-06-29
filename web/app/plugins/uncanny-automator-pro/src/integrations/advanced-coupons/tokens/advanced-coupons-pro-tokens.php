<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Advanced_Coupons_Pro_Tokens
 *
 * @package Uncanny_Automator
 */
class Advanced_Coupons_Pro_Tokens {

	public function __construct() {

		add_filter(
			'automator_maybe_trigger_acfwc_acfwccreditlimit_tokens',
			array( $this, 'trigger_acfwc_trigger_tokens_func' ),
			20,
			2
		);

		add_filter(
			'automator_maybe_trigger_acfwc_acfwclifetimecreditlimit_tokens',
			array( $this, 'trigger_acfwc_trigger_tokens_func' ),
			20,
			2
		);

		add_filter(
			'automator_maybe_parse_token',
			array(
				$this,
				'acfwc_parse_tokens',
			),
			20,
			6
		);

	}

	public function trigger_acfwc_trigger_tokens_func( $tokens = array(), $args = array() ) {
		$fields   = array();
		$fields[] = array(
			'tokenId'         => 'USERTOTALCREDIT',
			'tokenName'       => __( "User's total store credit", 'uncanny-automator' ),
			'tokenType'       => 'text',
			'tokenIdentifier' => $args['meta'],
		);
		$fields[] = array(
			'tokenId'         => 'USERLIFETIMECREDIT',
			'tokenName'       => __( "User's lifetime store credit", 'uncanny-automator' ),
			'tokenType'       => 'text',
			'tokenIdentifier' => $args['meta'],
		);

		return array_merge( $fields, $tokens );
	}

	/**
	 * This method is used to parse tokens.
	 *
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return mixed|string
	 */
	public function acfwc_parse_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( ! $pieces ) {
			return $value;
		}

		if ( in_array( 'ACFWCCREDITLIMIT', $pieces, true ) ) {
			return floatval( $this->get_token_value( $pieces ) );
		}

		if ( in_array( 'ACFWCLIFETIMECREDITLIMIT', $pieces, true ) ) {
			return floatval( $this->get_token_value( $pieces ) );
		}

		if ( in_array( 'USERTOTALCREDIT', $pieces, true ) ) {
			return floatval( $this->get_token_value( $pieces ) );
		}

		if ( in_array( 'USERLIFETIMECREDIT', $pieces, true ) ) {
			return floatval( $this->get_token_value( $pieces ) );
		}

		return $value;
	}

	/**
	 * This method is used to get token value from the database.
	 *
	 * @param $pieces
	 *
	 * @return mixed
	 */
	public function get_token_value( $pieces = array() ) {
		$meta_field = $pieces[2];
		$trigger_id = absint( $pieces[0] );
		$meta_value = $this->get_form_data_from_trigger_meta( $meta_field, $trigger_id );

		if ( is_array( $meta_value ) ) {
			$value = join( ', ', $meta_value );
		} else {
			$value = $meta_value;
		}

		return $value;
	}

	/**
	 * This method is used to get trigger meta from the database.
	 *
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
