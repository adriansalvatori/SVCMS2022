<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Memberpress_Pro_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class Memberpress_Pro_Tokens {

	/**
	 * Memberpress_Pro_Tokens constructor.
	 */
	public function __construct() {

		add_filter( 'automator_maybe_parse_token', array( $this, 'parse_memberpress_tokens' ), 20, 6 );
		add_filter( 'automator_maybe_parse_token', array( $this, 'parse_memberpress_ca_tokens' ), 20, 6 );
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 * @param $user_id
	 * @param $replace_args
	 *
	 * @return mixed|string
	 * @deprecated Use Free instead
	 */
	public function parse_memberpress_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( ! $pieces ) {
			return $value;
		}
		$matches = array(
			'MPPRODUCT',
			'MPPRODUCT_ID',
			'MPPRODUCT_URL',
		);

		$mepr_options = \MeprOptions::fetch();
		if ( $mepr_options->show_fname_lname ) {
			$matches[] = 'first_name';
			$matches[] = 'last_name';
		}

		if ( $mepr_options->show_address_fields && ! empty( $mepr_options->address_fields ) ) {
			foreach ( $mepr_options->address_fields as $address_field ) {
				$matches[] = $address_field->field_key;
			}
		}

		$custom_fields = $mepr_options->custom_fields;
		if ( ! empty( $custom_fields ) ) {
			foreach ( $custom_fields as $_field ) {
				$matches[] = $_field->field_key;
			}
		}

		if ( ! array_intersect( $matches, $pieces ) ) {
			return $value;
		}

		if ( empty( $trigger_data ) ) {
			return $value;
		}

		if ( ! isset( $pieces[2] ) ) {
			return $value;
		}
		foreach ( $trigger_data as $trigger ) {
			// all memberpress values will be saved in usermeta.
			$trigger_id     = absint( $trigger['ID'] );
			$trigger_log_id = absint( $replace_args['trigger_log_id'] );
			$parse_tokens   = array(
				'trigger_id'     => $trigger_id,
				'trigger_log_id' => $trigger_log_id,
				'user_id'        => $user_id,
			);

			$meta_key   = 'MPPRODUCT';
			$product_id = Automator()->db->trigger->get_token_meta( $meta_key, $parse_tokens );
			if ( empty( $product_id ) ) {
				continue;
			}
			switch ( $pieces[2] ) {
				case 'MPPRODUCT':
					$value = get_the_title( $product_id );
					break;
				case 'MPPRODUCT_ID':
					$value = absint( $product_id );
					break;
				case 'MPPRODUCT_URL':
					$value = get_the_permalink( $product_id );
					break;
				default:
					$value = get_user_meta( $user_id, $pieces[2], true );
					break;
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
	 * @param $replace_args
	 *
	 * @return mixed|string
	 * @deprecated Use Free instead
	 */
	public function parse_memberpress_ca_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( empty( $pieces ) ) {
			return $value;
		}

		if ( in_array( 'MPCAPARENTACC', $pieces ) || in_array( 'MPCAADDSUBACC', $pieces ) ) {
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

		return $value;
	}

	/**
	 * @param $meta_key
	 * @param $trigger_id
	 * @param $trigger_log_id
	 * @param null $user_id
	 *
	 * @return mixed|string
	 */
	public function get_trigger_log_meta_value( $meta_key, $trigger_id, $trigger_log_id, $user_id = null ) {
		if ( empty( $meta_key ) || empty( $trigger_id ) || empty( $trigger_log_id ) ) {
			return '';
		}
		global $wpdb;
		$qry        = $wpdb->prepare( "SELECT meta_value
														FROM {$wpdb->prefix}uap_trigger_log_meta
														WHERE 1 = 1
														AND user_id = %d
														AND meta_key = %s
														AND automator_trigger_id = %d
														AND automator_trigger_log_id = %d
														LIMIT 0,1", $user_id, $meta_key, $trigger_id, $trigger_log_id );
		$meta_value = $wpdb->get_var( $qry );
		if ( ! empty( $meta_value ) ) {
			return maybe_unserialize( $meta_value );
		}

		return '';
	}
}
