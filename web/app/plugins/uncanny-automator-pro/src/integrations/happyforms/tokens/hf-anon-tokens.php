<?php

namespace Uncanny_Automator_Pro;


/**
 * Class Hf_Anon_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class Hf_Anon_Tokens {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'HF';

	public function __construct() {
		// add_filter( 'automator_maybe_trigger_hf_anonhfform_tokens', [ $this, 'hf_possible_tokens' ], 20, 2 );
		add_filter( 'automator_maybe_parse_token', [ $this, 'hf_token' ], 20, 6 );
	}

	/**
	 * Only load this integration and its triggers and actions if the related
	 * plugin is active
	 *
	 * @param bool $status status of plugin.
	 * @param string $plugin plugin code.
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $plugin ) {

		if ( self::$integration === $plugin ) {
			if ( class_exists( 'FrmHooksController' ) ) {
				$status = true;
			} else {
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * Prepare tokens.
	 *
	 * @param array $tokens .
	 * @param array $args .
	 *
	 * @return array
	 */
	public function hf_possible_tokens( $tokens = array(), $args = array() ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}

		$form_id             = $args['value'];
	   $trigger_integration = $args['integration'];
	   $trigger_meta        = $args['meta'];

	   if ( ! empty( $form_id ) && 0 !== $form_id && is_numeric( $form_id ) ) {
	      $form_controller = happyforms_get_form_controller();
	      $form            = $form_controller->get( $form_id );
	      if ( $form ) {
	         $fields = array();
	         $meta   = $form['parts'];
	         if ( is_array( $meta ) && ! empty( $meta ) ) {
	            foreach ( $meta as $field ) {
	               $input_id   = $field['id'];
	               $field_type = 'text';
	               if ( 'int' === $field['type'] || 'numeric' === $field['type'] || 'number' === $field['type'] ) {
	                  $field_type = 'int';
	               }
	               if ( 'email' === $field['type'] ) {
	                  $field_type = 'email';
	               }
					$input_title = empty( $field['label'] ) ? $field['type'] : $field['label'];
					$token_id    = "$form_id|$input_id";
	               $fields[]    = array(
	                  'tokenId'         => $token_id,
	                  'tokenName'       => $input_title,
	                  'tokenType'       => $field_type,
	                  'tokenIdentifier' => $trigger_meta,
	               );
	            }
	         }
	         $tokens = array_merge( $tokens, $fields );
	      }
	   }

	   return $tokens;
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
	public function hf_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( $pieces ) {
			if ( in_array( 'ANONHFFORM', $pieces, true ) || in_array( 'HFSUBMITFIELD', $pieces, true ) || in_array( 'ANONHFSUBMITFIELD', $pieces, true ) ) {
				global $wpdb;
				if ( ! empty( $trigger_data ) ) {
					foreach ( $trigger_data as $trigger ) {
						$trigger_id   = $pieces[0];
						$trigger_meta = $pieces[1];
						$field        = $pieces[2];
						if ( $trigger_id == $trigger['ID'] ) {
							// check if readable meta exist.
							if ( ! empty( $trigger['meta'] ) ) {
								if ( isset( $trigger['meta'][ $field . '_readable' ] ) ) {
									$value = $trigger['meta'][ $field . '_readable' ];
								} elseif ( isset( $trigger['meta'][ $field ] ) ) {
									$value = $trigger['meta'][ $field ];
								}
							}
							if ( empty( $value ) ) {
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
				}
			}
		}

		return $value;
	}
}
