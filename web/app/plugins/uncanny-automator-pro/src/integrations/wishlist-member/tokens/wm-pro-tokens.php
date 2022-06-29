<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Wm_Pro_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class Wm_Pro_Tokens {

	/**
	 * Wm_Tokens constructor.
	 */
	public function __construct() {
		add_filter( 'automator_maybe_trigger_wishlistmember_wlmforms_tokens', array( $this, 'wlm_possible_tokens' ), 20, 2 );
		add_filter( 'automator_maybe_parse_token', array( $this, 'parse_wm_token' ), 20, 6 );
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 *
	 * @param int $user_id
	 * @param $replace_args
	 *
	 * @return mixed
	 */
	public function parse_wm_token( $value, $pieces, $recipe_id, $trigger_data, $user_id = 0, $replace_args = array() ) {
		$tokens = array(
			'WMFORMS',
			'WLMFORMS',
			'SPECIFICVALUE',
			'SPECIFICFIELD',
			'WMMEMBERSHIPLEVELS',
		);

		$piece = 'WLMFORMS';
		global $wpdb;
		if ( $pieces && isset( $pieces[1] ) && $piece === $pieces[1] ) {
			if ( ! empty( $trigger_data ) ) {
				foreach ( $trigger_data as $trigger ) {
					if ( $replace_args['trigger_id'] === $trigger['ID'] ) {
						$meta_value = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->prefix}uap_trigger_log_meta WHERE meta_key LIKE 'parsed_data' AND automator_trigger_id = {$trigger['ID']} AND automator_trigger_log_id = {$replace_args['trigger_log_id']} AND user_id = {$replace_args['user_id']} ORDER BY ID DESC LIMIT 0,1" );
						if ( ! empty( $meta_value ) ) {
							$meta_value = maybe_unserialize( $meta_value );
							if ( isset( $meta_value[ $pieces[2] ] ) ) {
								$value = $meta_value[ $pieces[2] ];
							}
						}
					}
				}
			}
		}

		if ( $pieces && isset( $pieces[2] ) ) {
			$meta_field = $pieces[2];
			if ( ! empty( $meta_field ) && in_array( $meta_field, $tokens, true ) ) {
				if ( $trigger_data ) {
					foreach ( $trigger_data as $trigger ) {
						$meta_value = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->prefix}uap_trigger_log_meta WHERE meta_key LIKE '%{$meta_field}%' AND automator_trigger_id = {$trigger['ID']} AND automator_trigger_log_id = {$replace_args['trigger_log_id']} AND user_id = {$replace_args['user_id']} ORDER BY ID DESC LIMIT 0,1" );
						if ( ! empty( $meta_value ) ) {
							$value = maybe_unserialize( $meta_value );
						}
					}
				}
			}
		}

		return $value;
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function wlm_possible_tokens( $tokens = array(), $args = array() ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}
		$form_id      = trim( $args['value'] );
		$trigger_meta = $args['meta'];
		if ( empty( $form_id ) ) {
			return $tokens;
		}
		$fields = array();

		global $wpdb, $uncanny_automator;
		if ( 'default' !== $form_id ) {
			$form       = $wpdb->get_var( "SELECT option_value FROM `{$wpdb->prefix}wlm_options` WHERE `option_name` LIKE '%{$form_id}%' ORDER BY `option_name` ASC" );
			$form_value = maybe_unserialize( wlm_serialize_corrector( $form ) );
			if ( ! isset( $form_value['form_dissected'] ) ) {
				return $tokens;
			}
			if ( ! isset( $form_value['form_dissected']['fields'] ) ) {
				return $tokens;
			}
			$form_fields = $form_value['form_dissected']['fields'];
			if ( is_array( $form_fields ) ) {
				foreach ( $form_fields as $field ) {
					if ( ! isset( $field['attributes']['type'] ) ) {
						$type = 'text';
					} else {
						$type = $field['attributes']['type'];
					}
					if ( 'password' !== $type ) {
						$fields[] = array(
							'tokenId'         => $field['attributes']['name'],
							'tokenName'       => str_replace( ':', '', $field['label'] ),
							'tokenType'       => 'text',
							'tokenIdentifier' => $trigger_meta,
						);
					}
				}
			}
		} elseif ( 'default' === $form_id ) {
			$form_fields = $uncanny_automator->helpers->recipe->wishlist_member->pro->get_form_fields();
			if ( is_array( $form_fields ) ) {
				foreach ( $form_fields as $key => $field ) {
					$fields[] = array(
						'tokenId'         => $key,
						'tokenName'       => $field,
						'tokenType'       => 'text',
						'tokenIdentifier' => $trigger_meta,
					);
				}
			}
		}
		if ( ! empty( $fields ) ) {
			$tokens = array_merge( $tokens, $fields );
		}

		return $tokens;
	}

}
