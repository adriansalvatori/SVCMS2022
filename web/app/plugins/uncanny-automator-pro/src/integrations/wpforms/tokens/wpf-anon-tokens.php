<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Wpf_Tokens;

/**
 * Class Wpf_Anon_Tokens
 * @package Uncanny_Automator_Pro
 */
class Wpf_Anon_Tokens extends Wpf_Tokens {
	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WPF';

	/**
	 *
	 */
	public function __construct() {
		add_filter( 'automator_maybe_parse_token', array( $this, 'wpf_pro_token' ), 200, 6 );
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
	public function wpf_pro_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( empty( $pieces ) ) {
			return $value;
		}
		if ( ! in_array( 'WPFSUBMITFIELD', $pieces, true ) && ! in_array( 'ANONWPFSUBMITFIELD', $pieces, true ) ) {
			return $value;
		}
		if ( ! isset( $pieces[2] ) ) {
			return $value;
		}
		$field = $pieces[2];
		// Form specific field
		if ( 'WPFSUBMITFIELD' === $field || 'ANONWPFSUBMITFIELD' === $field ) {
			if ( $trigger_data ) {
				foreach ( $trigger_data as $trigger ) {
					if ( array_key_exists( $field . '_readable', $trigger['meta'] ) ) {
						return $trigger['meta'][ $field . '_readable' ];
					}
				}
			}
		}

		if ( 'ANONWPFFORMS_ID' === $field ) {
			if ( $trigger_data ) {
				foreach ( $trigger_data as $trigger ) {
					if ( array_key_exists( 'ANONWPFFORMS', $trigger['meta'] ) ) {
						return $trigger['meta']['ANONWPFFORMS'];
					}
				}
			}
		}

		// Form specific field
		if ( 'SUBVALUE' === $field ) {
			if ( $trigger_data ) {
				foreach ( $trigger_data as $trigger ) {
					if ( array_key_exists( $field, $trigger['meta'] ) ) {
						return $trigger['meta'][ $field ];
					}
				}
			}
		}

		return $value;
	}

}
