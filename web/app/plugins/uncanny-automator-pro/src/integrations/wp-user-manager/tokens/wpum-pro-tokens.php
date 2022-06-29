<?php

namespace Uncanny_Automator_Pro;


use WPUM_Field;
use WPUM_Registration_Form;

/**
 * Class Wpum_Pro_Tokens
 * @package Uncanny_Automator_Pro
 */
class Wpum_Pro_Tokens {

	/**
	 * Wpum_Tokens constructor.
	 */
	public function __construct() {

		add_filter( 'automator_maybe_trigger_wpusermanager_wpumfieldvalue_tokens', [
			$this,
			'wpum_possible_tokens',
		], 20, 2 );

		add_filter( 'automator_maybe_trigger_wpusermanager_wpumuseraccupdate_tokens', [
			$this,
			'wpum_profile_possible_tokens',
		], 20, 2 );

		add_filter( 'automator_maybe_trigger_wpusermanager_wpumuserapproved_tokens', [
			$this,
			'wpum_fields_possible_tokens',
		], 20, 2 );

		add_filter( 'automator_maybe_trigger_wpusermanager_wpumuserrejected_tokens', [
			$this,
			'wpum_fields_possible_tokens',
		], 20, 2 );

		add_filter( 'automator_maybe_trigger_wpusermanager_wpumjoinsgroup_tokens', [
			$this,
			'wpum_fields_possible_tokens',
		], 20, 2 );

		add_filter( 'automator_maybe_trigger_wpusermanager_wpumleavesgroup_tokens', [
			$this,
			'wpum_fields_possible_tokens',
		], 20, 2 );

		add_filter( 'automator_maybe_trigger_wpusermanager_wpumrequestapproved_tokens', [
			$this,
			'wpum_fields_possible_tokens',
		], 20, 2 );

		add_filter( 'automator_maybe_trigger_wpusermanager_wpumrequestrejected_tokens', [
			$this,
			'wpum_fields_possible_tokens',
		], 20, 2 );

		add_filter( 'automator_maybe_trigger_wpusermanager_wpumuserverify_tokens', [
			$this,
			'wpum_fields_possible_tokens',
		], 20, 2 );

		add_filter( 'automator_maybe_parse_token', [ $this, 'parse_wpum_tokens' ], 20, 6 );
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function wpum_possible_tokens( $tokens = [], $args = [] ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}
		$form_id      = absint( $args['triggers_meta']['WPUMFORMS'] );
		$trigger_meta = $args['meta'];

		if ( empty( $form_id ) ) {
			return $tokens;
		}

		$form = new WPUM_Registration_Form( $form_id );

		if ( ! $form->exists() ) {
			return $tokens;
		}

		if ( $form->exists() ) {
			$fields        = [];
			$stored_fields = $form->get_fields();

			if ( is_array( $stored_fields ) && ! empty( $stored_fields ) ) {
				foreach ( $stored_fields as $field ) {
					$stored_field = new WPUM_Field( $field );
					if ( $stored_field->exists() ) {
						$fields[] = [
							'tokenId'         => $stored_field->get_primary_id(),
							'tokenName'       => $stored_field->get_name(),
							'tokenType'       => $stored_field->get_type(),
							'tokenIdentifier' => $trigger_meta,
						];
					}
				}
			}
		}

		$tokens = array_merge( $tokens, $fields );

		return $tokens;
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function wpum_profile_possible_tokens( $tokens = [], $args = [] ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}
		$trigger_meta = $args['meta'];

		$account_fields = WPUM()->fields->get_fields(
			[
				'group_id' => 1,
				'orderby'  => 'field_order',
				'order'    => 'ASC',
			]
		);

		foreach ( $account_fields as $field ) {

			$field = new WPUM_Field( $field );

			if ( $field->exists() && $field->get_meta( 'editing' ) == 'public' &&
			     $field->get_primary_id() !== 'user_password' ) {

				// Skip the avatar field if disabled.
				if ( $field->get_primary_id() == 'user_avatar' && ! wpum_get_option( 'custom_avatars' ) ) {
					continue;
				}
				$fields[] = [
					'tokenId'         => $field->get_primary_id(),
					'tokenName'       => $field->get_name(),
					'tokenType'       => $field->get_type(),
					'tokenIdentifier' => $trigger_meta,
				];
			}
		}

		$tokens = array_merge( $tokens, $fields );

		return $tokens;
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function wpum_fields_possible_tokens( $tokens = [], $args = [] ) {
		if ( ! automator_pro_do_identify_tokens() ) {
			return $tokens;
		}
		$trigger_meta = $args['meta'];

		$all_fields = WPUM()->fields->get_fields(
			[
				'group_id' => false,
				'orderby'  => 'field_order',
				'order'    => 'ASC',
			]
		);

		foreach ( $all_fields as $field ) {

			$field = new WPUM_Field( $field );

			if ( $field->exists() && $field->get_meta( 'editing' ) == 'public' &&
			     $field->get_primary_id() !== 'user_password' ) {

				// Skip the avatar field if disabled.
				if ( $field->get_primary_id() == 'user_avatar' && ! wpum_get_option( 'custom_avatars' ) ) {
					continue;
				}
				$fields[] = [
					'tokenId'         => $field->get_primary_id(),
					'tokenName'       => $field->get_name(),
					'tokenType'       => $field->get_type(),
					'tokenIdentifier' => $trigger_meta,
				];
			}
		}

		$tokens = array_merge( $tokens, $fields );

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
	 * @return mixed
	 */
	public function parse_wpum_tokens( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {
		if ( $pieces ) {
			if ( in_array( 'WPUMFIELDVALUE', $pieces ) ||
			     in_array( 'WPUMUSERREGISTERS', $pieces )
			) {
				global $wpdb;
				$trigger_id     = $pieces[0];
				$trigger_meta   = $pieces[2];
				$trigger_log_id = isset( $replace_args['trigger_log_id'] ) ? absint( $replace_args['trigger_log_id'] ) : 0;
				$entry          = $wpdb->get_var( "SELECT meta_value
													FROM {$wpdb->prefix}uap_trigger_log_meta
													WHERE meta_key = '{$trigger_meta}'
													AND automator_trigger_log_id = {$trigger_log_id}
													AND automator_trigger_id = {$trigger_id}
													LIMIT 0,1" );

				$value = maybe_unserialize( $entry );
			} elseif ( in_array( 'WPUMUVAPPROVED', $pieces ) || in_array( 'WPUMUSERAPPROVED', $pieces ) ||
			           in_array( 'WPUMUSERREJECTED', $pieces ) || in_array( 'WPUMUVEMAILVERIFY', $pieces ) ||
			           in_array( 'WPUMUVREJECTED', $pieces ) || in_array( 'WPUMUSERVERIFY', $pieces ) ||
			           in_array( 'WPUMGJOINED', $pieces ) || in_array( 'WPUMJOINSGROUP', $pieces ) ||
			           in_array( 'WPUMGLEAVED', $pieces ) || in_array( 'WPUMLEAVESGROUP', $pieces ) ||
			           in_array( 'WPUMGAPPROVED', $pieces ) || in_array( 'WPUMREQUESTAPPROVED', $pieces ) ||
			           in_array( 'WPUMGREJECTED', $pieces ) || in_array( 'WPUMREQUESTREJECTED', $pieces ) ||
			           in_array( 'WPUMUSERACCUPDATE', $pieces ) || in_array( 'WPUMACCINFO', $pieces )
			) {
				if ( $pieces[2] == 'WPUMGJOINED' || $pieces[2] == 'WPUMGLEAVED' || $pieces[2] == 'WPUMGAPPROVED' ||
				     $pieces[2] == 'WPUMGREJECTED' ) {
					global $wpdb;
					$trigger_id     = $pieces[0];
					$trigger_meta   = $pieces[2];
					$trigger_log_id = isset( $replace_args['trigger_log_id'] ) ? absint( $replace_args['trigger_log_id'] ) : 0;
					$entry          = $wpdb->get_var( "SELECT meta_value
													FROM {$wpdb->prefix}uap_trigger_log_meta
													WHERE meta_key = '{$trigger_meta}'
													AND automator_trigger_log_id = {$trigger_log_id}
													AND automator_trigger_id = {$trigger_id}
													LIMIT 0,1" );

					$value = maybe_unserialize( $entry );
				} else {
					$user  = get_user_by( 'id', $user_id );
					$field = $pieces[2];

					$entry = $this->get_field_value( $user, $field );
					$value = maybe_unserialize( $entry );
				}

			}
		}

		return $value;
	}

	/**
	 * @param $user
	 * @param $field
	 *
	 * @return mixed
	 */
	function get_field_value( $user, $field ) {
		switch ( $field ) {
			case 'user_firstname':
				$value = $user->user_firstname;
				break;
			case 'user_lastname':
				$value = $user->user_lastname;
				break;
			case 'user_email':
				$value = $user->user_email;
				break;
			case 'user_nickname':
				$value = get_user_meta( $user->ID, 'nickname', true );
				break;
			case 'user_website':
				$value = $user->user_url;
				break;
			case 'user_description':
				$value = get_user_meta( $user->ID, 'description', true );
				break;
			case 'user_displayname':
				$value = $user->display_name;
				break;
			case 'user_avatar':
				$value = carbon_get_user_meta( $user->ID, 'current_user_avatar' );
				break;
			case 'user_cover':
				$value = carbon_get_user_meta( $user->ID, 'user_cover' );
				break;
			default:
				global $wpdb;
				$field_id = $wpdb->get_var( "SELECT id FROM {$wpdb->prefix}wpum_fields WHERE type = '{$field}' LIMIT 0,1" );
				$value    = get_user_meta( $user->ID, WPUM()->field_meta->get_meta( $field_id, 'user_meta_key' ),
					true );
		}

		return $value;
	}

}
