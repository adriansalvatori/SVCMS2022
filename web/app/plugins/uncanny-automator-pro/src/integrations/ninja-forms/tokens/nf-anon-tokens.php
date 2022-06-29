<?php

namespace Uncanny_Automator_Pro;


use Uncanny_Automator\Nf_Tokens;
use function Ninja_Forms;

/**
 * Class Nf_Anon_Tokens
 * @package Uncanny_Automator_Pro
 */
class Nf_Anon_Tokens extends Nf_Tokens {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'NF';

	public function __construct() {
		// add_filter( 'automator_maybe_trigger_nf_anonnfforms_tokens', [ $this, 'nf_possible_tokens' ], 20, 2 );
		add_filter( 'automator_maybe_parse_token', [ $this, 'nf_token' ], 20, 6 );
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
			if ( class_exists( 'Ninja_Forms' ) ) {
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
	function nf_possible_tokens( $tokens = [], $args = [] ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}
		$form_id             = $args['value'];
		$trigger_integration = $args['integration'];
		$trigger_meta        = $args['meta'];

		$form_ids = [];
		if ( ! empty( $form_id ) && 0 !== $form_id && is_numeric( $form_id ) ) {
			$form = Ninja_Forms()->form( $form_id )->get();
			if ( $form ) {
				$form_ids[] = $form->get_id();
			}
		}

		if ( empty( $form_ids ) ) {
			$forms = Ninja_Forms()->form()->get_forms();
			foreach ( $forms as $form ) {
				$form_ids[] = $form->get_id();
			}
		}

		if ( ! empty( $form_ids ) ) {
			foreach ( $form_ids as $form_id ) {
				$fields = [];
				$meta   = Ninja_Forms()->form( $form_id )->get_fields();
				if ( is_array( $meta ) ) {
					foreach ( $meta as $field ) {
						if ( $field->get_setting( 'type' ) !== 'submit' ) {
							$input_id    = $field->get_id();
							$input_title = $field->get_setting( 'label' );
							$token_id    = "$form_id|$input_id";
							$fields[]    = [
								'tokenId'         => $token_id,
								'tokenName'       => $input_title,
								'tokenType'       => $field->get_setting( 'type' ),
								'tokenIdentifier' => $trigger_meta,
							];
						}
					}
				}
				$tokens = array_merge( $tokens, $fields );
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
	 * @return null|string
	 */
	public function nf_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( empty( $pieces ) ) {
			return $value;
		}
		if ( empty( $trigger_data ) ) {
			return $value;
		}
		$piece = 'ANONNFFORMS';
		if ( ! in_array( $piece, $pieces, false ) ) {
			return $value;
		}

		$trigger_id     = absint( $pieces[0] );
		$trigger_meta   = $pieces[1];
		$field          = $pieces[2];
		$trigger_log_id = $replace_args['trigger_log_id'];
		$entry          = Automator()->helpers->recipe->get_form_data_from_trigger_meta( $trigger_meta, $trigger_id, $trigger_log_id, $user_id );
		$entry          = maybe_unserialize( $entry );
		$to_match       = "{$trigger_id}:{$trigger_meta}:{$field}";
		if ( is_array( $entry ) && key_exists( $to_match, $entry ) ) {
			$value = $entry[ $to_match ];
		}
		if ( is_array( $value ) ) {
			$value = join( ', ', $value );
		}


		return $value;
	}
}
