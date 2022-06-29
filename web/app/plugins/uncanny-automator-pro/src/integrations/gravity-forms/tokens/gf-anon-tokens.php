<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Gf_Anon_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class Gf_Anon_Tokens {

	public function __construct() {
		add_filter(
			'automator_maybe_trigger_gf_gfusercreated_tokens',
			array(
				$this,
				'gf_users_possible_tokens',
			),
			20,
			2
		);
		add_filter( 'automator_maybe_trigger_gf_gfforms_tokens', array( $this, 'gf_possible_tokens' ), 20, 2 );
		add_filter( 'automator_maybe_trigger_gf_anongfforms_tokens', array( $this, 'gf_possible_tokens' ), 20, 2 );
		add_filter( 'automator_maybe_parse_token', array( $this, 'gf_token' ), 20, 6 );
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
	public function gf_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( empty( $pieces ) ) {
			return $value;
		}
		$trigger_meta_validations = apply_filters(
			'automator_pro_gravity_forms_validate_trigger_meta_pieces',
			array(
				'ANONGFFORMS',
				'ANONSUBFIELD',
				'GFUSERCREATED',
				'USERCREATED',
				'GFSUBFORMPAYMENT',
				'ANONGFSUBFORMPAYMENT',
			),
			array(
				'pieces'       => $pieces,
				'recipe_id'    => $recipe_id,
				'trigger_data' => $trigger_data,
				'user_id'      => $user_id,
				'replace_args' => $replace_args,
			)
		);
		if ( ! array_intersect( $trigger_meta_validations, $pieces ) ) {
			return $value;
		}
		if ( in_array( 'ANONGFFORMS', $pieces, true ) || in_array( 'ANONSUBFIELD', $pieces, true ) ) {
			if ( isset( $pieces[2] ) && 'ANONGFFORMS' === $pieces[2] ) {
				$t_data   = array_shift( $trigger_data );
				$form_id  = $t_data['meta'][ $pieces[2] ];
				$forminfo = \RGFormsModel::get_form( $form_id );

				return $forminfo->title;
			}

			if ( isset( $pieces[2] ) && 'ANONGFFORMS_ID' === $pieces[2] ) {
				$t_data = array_shift( $trigger_data );

				return $t_data['meta']['ANONGFFORMS'];
			}

			if ( isset( $pieces[2] ) && 'SUBVALUE' === $pieces[2] ) {
				$t_data = array_shift( $trigger_data );

				return $t_data['meta'][ $pieces[2] ];
			}
			if ( isset( $pieces[2] ) && 'ANONSUBFIELD' === $pieces[2] ) {
				$t_data = array_shift( $trigger_data );

				return $t_data['meta'][ $pieces[2] . '_readable' ];
			}
		} elseif ( in_array( 'GFUSERCREATED', $pieces, true ) || in_array( 'USERCREATED', $pieces, true ) ) {
			$meta_field = $pieces[2];
			if ( $trigger_data ) {
				foreach ( $trigger_data as $trigger ) {
					global $wpdb;
					$get_user_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}uap_trigger_log_meta WHERE meta_key = 'user_id' AND automator_trigger_id = %d ORDER BY ID DESC LIMIT 0,1", $trigger['ID'] ) );
					$get_user_id = maybe_unserialize( $get_user_id );
					$get_user    = get_userdata( $get_user_id );
					switch ( $meta_field ) {
						case 'USERNAME':
							$value = $get_user->user_login;
							break;
						case 'FIRSTNAME':
							$value = $get_user->user_firstname;
							break;
						case 'LASTNAME':
							$value = $get_user->user_lastname;
							break;
						case 'EMAIL':
							$value = $get_user->user_email;
							break;
					}
				}
			}
		} elseif ( in_array( 'GFSUBFORMPAYMENT', $pieces, true ) || in_array( 'ANONGFSUBFORMPAYMENT', $pieces, true ) ) {
			if ( isset( $pieces[2] ) && 'ANONGFFORMS_ID' === $pieces[2] ) {
				$t_data = array_shift( $trigger_data );

				return $t_data['meta']['ANONGFFORMS'];
			}

			if ( isset( $pieces[2] ) && 'GFFORMS_ID' === $pieces[2] ) {
				$t_data = array_shift( $trigger_data );

				return $t_data['meta']['GFFORMS'];
			}
		}

		return $value;
	}

	/**
	 * @param $tokens
	 * @param $args
	 *
	 * @return array|array[]|mixed
	 */
	public function gf_users_possible_tokens( $tokens = array(), $args = array() ) {
		$trigger_meta = $args['meta'];

		$fields = array(
			array(
				'tokenId'         => 'USERNAME',
				'tokenName'       => __( 'Username', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
			array(
				'tokenId'         => 'FIRSTNAME',
				'tokenName'       => __( 'First name', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
			array(
				'tokenId'         => 'LASTNAME',
				'tokenName'       => __( 'Last name', 'uncanny-automator-pro' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => $trigger_meta,
			),
			array(
				'tokenId'         => 'EMAIL',
				'tokenName'       => __( 'Email', 'uncanny-automator-pro' ),
				'tokenType'       => 'email',
				'tokenIdentifier' => $trigger_meta,
			),
		);
		$tokens = array_merge( $tokens, $fields );

		return $tokens;
	}

	/**
	 * @param $tokens
	 * @param $args
	 *
	 * @return array|array[]
	 */
	public function gf_possible_tokens( $tokens = array(), $args = array() ) {
		foreach ( $tokens as $k => $token ) {
			if ( preg_match( '/Cardholder Name/', $token['tokenName'] ) ) {
				unset( $tokens[ $k ] );
				$tokens = array_values( $tokens );
			}
		}

		return $tokens;
	}

}
