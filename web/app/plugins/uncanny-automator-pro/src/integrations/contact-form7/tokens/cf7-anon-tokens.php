<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Cf7_Tokens;
use WPCF7_ContactForm;

/**
 * Class Cf7_Anon_Tokens
 * @package Uncanny_Automator_Pro
 */
class Cf7_Anon_Tokens extends Cf7_Tokens {

	/**
	 * Cf7_Anon_Tokens constructor.
	 */
	public function __construct() {

		// add_filter( 'automator_maybe_trigger_cf7_anoncf7forms_tokens', [ $this, 'cf7_possible_tokens' ], 20, 2 );

		add_filter( 'automator_maybe_parse_token', [ $this, 'parse_cf7_anon_token' ], 21, 6 );

		//save submission to user meta
		add_action( 'automator_save_anon_cf7_form', [ $this, 'automator_save_anon_cf7_form_func' ], 20, 3 );
	}

	/**
	 * @param WPCF7_ContactForm $contact_form
	 * @param $recipes
	 * @param $args
	 */
	public function automator_save_anon_cf7_form_func( WPCF7_ContactForm $contact_form, $recipes, $args ) {
		if ( is_array( $args ) ) {
			foreach ( $args as $trigger_result ) {
				//$trigger_result = array_pop( $args );
				if ( true === $trigger_result['result'] ) {
					global $uncanny_automator;
					if ( $recipes && $contact_form instanceof WPCF7_ContactForm ) {
						foreach ( $recipes as $recipe ) {
							$triggers = $recipe['triggers'];
							if ( $triggers ) {
								foreach ( $triggers as $trigger ) {
									$trigger_id = $trigger['ID'];
									if ( ! key_exists( 'ANONCF7FORMS', $trigger['meta'] ) ) {
										continue;
									} else {
										$form_id        = (int) $trigger['meta']['ANONCF7FORMS'];
										$data           = $this->get_data_from_contact_form( $contact_form );
										$user_id        = (int) $trigger_result['args']['user_id'];
										$trigger_log_id = (int) $trigger_result['args']['get_trigger_id'];
										$run_number     = (int) $trigger_result['args']['run_number'];

										$args = [
											'user_id'        => $user_id,
											'trigger_id'     => $trigger_id,
											'meta_key'       => 'ANONCF7FORMS_' . $form_id,
											'meta_value'     => serialize( $data ),
											'run_number'     => $run_number, //get run number
											'trigger_log_id' => $trigger_log_id,
										];

										$uncanny_automator->insert_trigger_meta( $args );
									}
								}
							}
						}
					}
				}
			}
		}
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return mixed
	 */
	public function parse_cf7_anon_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		$piece = 'ANONCF7FORMS';
		if ( $pieces && in_array( $piece, $pieces ) && $trigger_data ) {
			$trigger_id     = (int) $replace_args['trigger_id'];
			$trigger_log_id = (int) $replace_args['trigger_log_id'];
			$token_info     = explode( '|', $pieces[2] );
			$form_id        = $token_info[0];
			$meta_key       = $token_info[1];
			$meta_field     = $piece . '_' . $form_id;
			$user_meta      = $this->get_form_data_from_anon_trigger_meta( $user_id, $meta_field, $trigger_id, $trigger_log_id );
			if ( ! empty( $user_meta ) && key_exists( trim( $meta_key ), $user_meta ) ) {
				if ( is_array( $user_meta[ $meta_key ] ) ) {
					$value = join( ', ', $user_meta[ $meta_key ] );
				} else {
					$value = $user_meta[ $meta_key ];
				}
			}
		}

		return $value;
	}

	/**
	 * @param $user_id
	 * @param $meta_key
	 * @param $trigger_id
	 * @param $trigger_log_id
	 *
	 * @return mixed|string
	 */
	public function get_form_data_from_anon_trigger_meta( $user_id, $meta_key, $trigger_id, $trigger_log_id ) {
		global $wpdb;
		if ( empty( $meta_key ) || empty( $trigger_id ) || empty( $trigger_log_id ) ) {
			return '';
		}

		$meta_value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}uap_trigger_log_meta WHERE user_id = %d AND meta_key = %s AND automator_trigger_id = %d AND automator_trigger_log_id = %d ORDER BY ID DESC LIMIT 0,1", $user_id, $meta_key, $trigger_id, $trigger_log_id ) );
		if ( ! empty( $meta_value ) ) {
			return maybe_unserialize( $meta_value );
		}

		return '';
	}
}