<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Wpff_Pro_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class Wpff_Pro_Tokens extends \Uncanny_Automator\Wpff_Tokens {

	/**
	 * Wpff_Pro_Tokens constructor.
	 */
	public function __construct() {

		//add_filter( 'automator_maybe_trigger_wpff_wpffforms_tokens', [ $this, 'wpff_possible_tokens' ], 20, 2 );

		add_filter( 'automator_maybe_parse_token', [ $this, 'wpff_anon_token' ], 20, 6 );
		add_filter( 'automator_maybe_parse_token', [ $this, 'wpff_token' ], 20, 6 );

	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function wpff_anon_possible_tokens( $tokens = [], $args = [] ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}

		$form_id      = isset( $args['triggers_meta']['ANONWPFFFORMS'] ) ? absint( $args['triggers_meta']['ANONWPFFFORMS'] ) : 0;
		$trigger_meta = $args['meta'];

		if ( $form_id ) {
			global $uncanny_automator;
			$new_tokens = $uncanny_automator->helpers->recipe->wp_fluent_forms->pro->create_tokens( $form_id, $trigger_meta );
			$tokens     = array_merge( $tokens, $new_tokens );
		}

		return $tokens;
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function wpff_possible_tokens_old( $tokens = [], $args = [] ) {

		$form_id      = isset( $args['triggers_meta']['WPFFFORMS'] ) ? absint( $args['triggers_meta']['WPFFFORMS'] ) : 0;
		$trigger_meta = $args['meta'];

		if ( $form_id ) {
			global $uncanny_automator;
			$new_tokens = $uncanny_automator->helpers->recipe->wp_fluent_forms->pro->create_tokens( $form_id, $trigger_meta );
			$exists     = array_column( $tokens, 'tokenId' );
			if ( $new_tokens ) {
				foreach ( $new_tokens as $k => $new_token ) {
					if ( ! in_array( $new_token['tokenId'], $exists, false ) ) {
						$tokens[] = $new_tokens[ $k ];
					}
				}
			}
		}

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
	 * @return string|null
	 */
	public function wpff_anon_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( $pieces ) {
			if ( 'ANONWPFFSUBFORM' === $pieces[1] || 'ANONWPFFFORMS' === $pieces[1] || 'ANONWPFFSUBFIELD' === $pieces[1] ) {
				if ( 'FORMFIELD' === $pieces[2] ) {

					$value = '';
					if ( isset( $trigger_data[0]['meta']['FORMFIELD_readable'] ) ) {
						$value = $trigger_data[0]['meta']['FORMFIELD_readable'];
					}
				} elseif ( 'FORMFIELDVALUE' === $pieces[2] ) {
					$value = '';
					if ( isset( $trigger_data[0]['meta']['FORMFIELDVALUE'] ) ) {
						$value = $trigger_data[0]['meta']['FORMFIELDVALUE'];
					}
				} elseif ( 'ANONWPFFFORMS' === $pieces[2] ) {

					$value = '';
					if ( isset( $trigger_data[0]['meta']['ANONWPFFFORMS_readable'] ) ) {
						$value = $trigger_data[0]['meta']['ANONWPFFFORMS_readable'];
					}
				} else {
					global $wpdb;
					$trigger_id     = $pieces[0];
					$trigger_meta   = $pieces[1];
					$field          = $pieces[2];
					$trigger_log_id = isset( $replace_args['trigger_log_id'] ) ? absint( $replace_args['trigger_log_id'] ) : 0;

					if ( absint( $field ) ) {
						// There is a form ID
						// We are looking for form data
						$meta_key = $trigger_meta;
					} else {
						//There is no form ID
						// We are looking for the for ID
						$meta_key = $field . '_ID';
					}

					$q = "SELECT meta_value
							FROM {$wpdb->prefix}uap_trigger_log_meta
							WHERE meta_key = '$meta_key'
							AND automator_trigger_log_id = $trigger_log_id
							AND automator_trigger_id = $trigger_id
							LIMIT 0, 1";

					$_entry   = $wpdb->get_var( $q );
					$entry    = maybe_unserialize( $_entry );
					$to_match = "{$trigger_id}:{$trigger_meta}:{$field}";

					if ( is_array( $entry ) && key_exists( $to_match, $entry ) ) {
						$value = $entry[ $to_match ];
					} else {
						// Added this block to gather Mutli select and/or checkbox values
						$v = array();
						$k = 0;
						while ( $k < 20 ) {
							if ( is_array( $entry ) && key_exists( "$to_match|$k", $entry ) ) {
								$v[] = $entry["$to_match|$k"];
							}
							$k ++;
						}
						$value = ! empty( $v ) ? join( ', ', $v ) : $entry;
					}
				}
			}
		}

		return $value;
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 *
	 * @return string|null
	 */
	public function wpff_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {

		if ( $pieces ) {
			if ( 'WPFFSUBFIELD' === $pieces[1] || 'WPFFFORMS' === $pieces[1] ) {
				if ( 'FORMFIELD' === $pieces[2] ) {

					$value = '';
					if ( isset( $trigger_data[0]['meta']['FORMFIELD_readable'] ) ) {
						$value = $trigger_data[0]['meta']['FORMFIELD_readable'];
					}
				} elseif ( 'FORMFIELDVALUE' === $pieces[2] ) {
					$value = '';
					if ( isset( $trigger_data[0]['meta']['FORMFIELDVALUE'] ) ) {
						$value = $trigger_data[0]['meta']['FORMFIELDVALUE'];
					}
				} elseif ( 'WPFFFORMS' === $pieces[2] ) {

					$value = '';
					if ( isset( $trigger_data[0]['meta']['WPFFFORMS_readable'] ) ) {
						$value = $trigger_data[0]['meta']['WPFFFORMS_readable'];
					}

				} else {

					global $wpdb;
					$trigger_id     = $pieces[0];
					$trigger_meta   = $pieces[1];
					$field          = $pieces[2];
					$trigger_log_id = isset( $replace_args['trigger_log_id'] ) ? absint( $replace_args['trigger_log_id'] ) : 0;

					if ( absint( $field ) ) {
						// There is a form ID
						// We are looking for form data
						$meta_key = $trigger_meta;
					} else {
						//There is no form ID
						// We are looking for the for ID
						$meta_key = $field . '_ID';
					}

					$q = "SELECT meta_value
													FROM {$wpdb->prefix}uap_trigger_log_meta
													WHERE meta_key = '$meta_key'
													AND automator_trigger_log_id = $trigger_log_id
													AND automator_trigger_id = $trigger_id
													LIMIT 0, 1";

					$_entry   = $wpdb->get_var( $q );
					$entry    = maybe_unserialize( $_entry );
					$to_match = "{$trigger_id}:{$trigger_meta}:{$field}";

					if ( is_array( $entry ) && key_exists( $to_match, $entry ) ) {
						$value = $entry[ $to_match ];
					} else {
						// Added this block to gather Mutli select and/or checkbox values
						$v = array();
						$k = 0;
						while ( $k < 20 ) {
							if ( key_exists( "$to_match|$k", $entry ) ) {
								$v[] = $entry["$to_match|$k"];
							}
							$k ++;
						}
						$value = ! empty( $v ) ? join( ', ', $v ) : '';
					}
				}
			}
		}

		return $value;
	}
}
