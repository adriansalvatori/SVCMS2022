<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Fi_Pro_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class Fi_Pro_Tokens {

	/**
	 * Fi_Anon_Tokens constructor.
	 */
	public function __construct() {
		add_filter( 'automator_maybe_parse_token', array( $this, 'fi_token' ), 20, 6 );
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
	public function fi_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( $pieces ) {
			if ( in_array( 'FIFORM', $pieces, true ) || in_array( 'FISUBMITFIELD', $pieces, true ) || in_array( 'FIUPDATEFIELD', $pieces, true ) || in_array( 'FISUBMITFORM', $pieces, true ) ) {

				global $wpdb;

				$trigger_id   = $pieces[0];
				$trigger_meta = $pieces[1];
				$field        = $pieces[2];

				if ( 'FIFORM' === $pieces[2] ) {

					if ( isset( $trigger_data[0]['meta']['FIFORM_readable'] ) ) {
						$value = $trigger_data[0]['meta']['FIFORM_readable'];
					}
				} elseif ( 'FISUBMITFIELD' === $pieces[2] ) {

					if ( isset( $trigger_data[0]['meta']['FISUBMITFIELD_readable'] ) ) {
						$value = $trigger_data[0]['meta']['FISUBMITFIELD_readable'];
					}
				} elseif ( 'SUBVALUE' === $pieces[2] ) {

					if ( isset( $trigger_data[0]['meta']['SUBVALUE'] ) ) {
						$value = $trigger_data[0]['meta']['SUBVALUE'];
					}
				} elseif ( 'FIFORM' === $pieces[2] ) {

					if ( isset( $trigger_data[0]['meta']['FIFORM_readable'] ) ) {
						$value = $trigger_data[0]['meta']['FIFORM_readable'];
					}
				} else {

					$trigger_log_id = isset( $replace_args['trigger_log_id'] ) ? absint( $replace_args['trigger_log_id'] ) : 0;

					$entry = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT meta_value 
							FROM {$wpdb->prefix}uap_trigger_log_meta
							WHERE meta_key = %s
							AND automator_trigger_log_id = %d 
							AND automator_trigger_id = %d 
							LIMIT 0, 1",
							$trigger_meta,
							$trigger_log_id,
							$trigger_id
						)
					);

					$entry = maybe_unserialize( $entry );

					$to_match = "{$trigger_id}:{$trigger_meta}:{$field}";

					if ( is_array( $entry ) && key_exists( $to_match, $entry ) ) {

						$value = $entry[ $to_match ];

						$field_params = explode( '|', $pieces[2] );

						if ( 'file' === $this->get_field_type( $field_params[1] ) ) {

							$value = esc_url( wp_get_attachment_url( $value ) );

						}
					}
				}
			}
		}

		$unserialize_value = maybe_unserialize( $value );

		if ( is_array( $unserialize_value ) && ! empty( $unserialize_value ) ) {

			$value = implode( ', ', $unserialize_value );

		}

		return $value;
	}

	/**
	 * Method get_field_type
	 *
	 * @param  array $pieces The token pieces.
	 * @return string The field type.
	 */
	protected function get_field_type( $field_id = 0 ) {

		$field = \FrmField::getOne( $field_id );

		return isset( $field->type ) ? $field->type : '';

	}

}
