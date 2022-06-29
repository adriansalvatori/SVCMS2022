<?php

namespace Uncanny_Automator_Pro;

/**
 * Class PeepSo_Pro_Tokens
 *
 * @package Uncanny_Automator_Pro
 */
class PeepSo_Pro_Tokens {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'PP';

	public function __construct() {

		add_filter(
			'automator_maybe_trigger_pp_tokens',
			array(
				$this,
				'peepso_possible_tokens_pro',
			),
			20,
			2
		);
		add_filter(
			'automator_maybe_parse_token',
			array(
				$this,
				'parse_peepso_pro_token',
			),
			20,
			6
		);
	}

	/**
	 * Only load this integration and its triggers and actions if the related
	 * plugin is active
	 *
	 * @param $status
	 * @param $code
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $code ) {

		if ( self::$integration === $code ) {
			if ( class_exists( 'PeePso' ) ) {
				$status = true;
			} else {
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * @param $user_id
	 * @param $meta_key
	 * @param $trigger_id
	 * @param $trigger_log_id
	 *
	 * @return mixed|string
	 */
	public function get_meta_data_from_trigger_meta( $user_id, $meta_key, $trigger_id, $trigger_log_id ) {
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

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function peepso_possible_tokens_pro( $tokens = array(), $args = array() ) {

		if ( 'PPUSERPUBLISHESANACTIVITYPOST' === $args['triggers_meta']['code'] ) {
			$fields = array(
				array(
					'tokenId'         => 'POSTBODY',
					'tokenName'       => __( 'Activity content', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPACTIVITYTOKENS',
				),
				array(
					'tokenId'         => 'POSTID',
					'tokenName'       => __( 'Activity ID', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPACTIVITYTOKENS',
				),
				array(
					'tokenId'         => 'POSTURL',
					'tokenName'       => __( 'Activity URL', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPACTIVITYTOKENS',
				),
			);
			$tokens = array_merge( $tokens, $fields );
		}

		if ( isset( $args['triggers_meta']['code'] ) && 'PPUSERUPDATESPECIFICFIELDTOSPECIFICVALUE' === $args['triggers_meta']['code'] ) {

			$fields = array(
				array(
					'tokenId'         => 'PPFIELD_NAME',
					'tokenName'       => __( 'Updated field name', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFIELD_NAME',
				),
				array(
					'tokenId'         => 'USR_AVATARURL',
					'tokenName'       => __( 'User avatar URL', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSERAVATARURL',
				),
				array(
					'tokenId'         => 'USR_GENDER',
					'tokenName'       => __( 'User gender', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_GENDER',
				),
				array(
					'tokenId'         => 'USR_BIRTHDATE',
					'tokenName'       => __( 'User birthdate', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_BIRTHDATE',
				),
				array(
					'tokenId'         => 'USR_FOLLOWERS',
					'tokenName'       => __( 'User total number of followers', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_FOLLOWERS',
				),
				array(
					'tokenId'         => 'USR_FOLLOWING',
					'tokenName'       => __( 'User total following count of followers', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_FOLLOWING',
				),
				array(
					'tokenId'         => 'USR_PROFILEURL',
					'tokenName'       => __( 'User profile URL', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_PROFILEURL',
				),
				array(
					'tokenId'         => 'USR_ABOUTME',
					'tokenName'       => __( 'User bio', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_ABOUTME',
				),
				array(
					'tokenId'         => 'USR_WEBSITE',
					'tokenName'       => __( 'User website address', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_WEBSITE',
				),
				array(
					'tokenId'         => 'USR_USERROLE',
					'tokenName'       => __( 'User PeepSo role', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_USERROLE',
				),
			);

			$tokens = array_merge( $tokens, $fields );
		}

		if ( isset( $args['triggers_meta']['code'] ) && 'USERUNFOLLOWSAUSER' === $args['triggers_meta']['code'] ) {
			$fields = array(
				array(
					'tokenId'         => 'AVATARURL',
					'tokenName'       => __( 'PeepSo member avatar URL', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPAVATARURL',
				),
				array(
					'tokenId'         => 'FL_USERID',
					'tokenName'       => __( 'PeepSo member user ID', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFL_USERID',
				),
				array(
					'tokenId'         => 'FL_USERNAME',
					'tokenName'       => __( 'PeepSo member username', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFL_USERNAME',
				),
				array(
					'tokenId'         => 'FL_FIRST_NAME',
					'tokenName'       => __( 'PeepSo member first name', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFL_FIRST_NAME',
				),
				array(
					'tokenId'         => 'FL_LAST_NAME',
					'tokenName'       => __( 'PeepSo member last name', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFL_LAST_NAME',
				),
				array(
					'tokenId'         => 'FL_GENDER',
					'tokenName'       => __( 'PeepSo member gender', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFL_GENDER',
				),
				array(
					'tokenId'         => 'FL_BIRTHDATE',
					'tokenName'       => __( 'PeepSo member birthdate', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFL_BIRTHDATE',
				),
				array(
					'tokenId'         => 'FL_FOLLOWERS',
					'tokenName'       => __( 'PeepSo member total number of followers', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFL_FOLLOWERS',
				),
				array(
					'tokenId'         => 'FL_FOLLOWING',
					'tokenName'       => __( 'PeepSo member total following count of followers', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFL_FOLLOWING',
				),
				array(
					'tokenId'         => 'FL_PROFILEURL',
					'tokenName'       => __( 'PeepSo member profile URL', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFL_PROFILEURL',
				),
				array(
					'tokenId'         => 'FL_EMAIL',
					'tokenName'       => __( 'PeepSo member email', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFL_EMAIL',
				),
				array(
					'tokenId'         => 'FL_ABOUTME',
					'tokenName'       => __( 'PeepSo member bio', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFL_ABOUTME',
				),
				array(
					'tokenId'         => 'FL_WEBSITE',
					'tokenName'       => __( 'PeepSo member website address', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFL_WEBSITE',
				),
				array(
					'tokenId'         => 'FL_ROLE',
					'tokenName'       => __( 'PeepSo member PeepSo role', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPFL_ROLE',
				),
				array(
					'tokenId'         => 'USR_AVATARURL',
					'tokenName'       => __( 'User avatar URL', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSERAVATARURL',
				),
				array(
					'tokenId'         => 'USR_GENDER',
					'tokenName'       => __( 'User gender', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_GENDER',
				),
				array(
					'tokenId'         => 'USR_BIRTHDATE',
					'tokenName'       => __( 'User birthdate', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_BIRTHDATE',
				),
				array(
					'tokenId'         => 'USR_FOLLOWERS',
					'tokenName'       => __( 'User total number of followers', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_FOLLOWERS',
				),
				array(
					'tokenId'         => 'USR_FOLLOWING',
					'tokenName'       => __( 'User total following count of followers', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_FOLLOWING',
				),
				array(
					'tokenId'         => 'USR_PROFILEURL',
					'tokenName'       => __( 'User profile URL', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_PROFILEURL',
				),
				array(
					'tokenId'         => 'USR_ABOUTME',
					'tokenName'       => __( 'User bio', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_ABOUTME',
				),
				array(
					'tokenId'         => 'USR_WEBSITE',
					'tokenName'       => __( 'User website address', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_WEBSITE',
				),
				array(
					'tokenId'         => 'USR_USERROLE',
					'tokenName'       => __( 'User PeepSo role', 'uncanny-automator-pro' ),
					'tokenType'       => 'text',
					'tokenIdentifier' => 'PPUSR_USERROLE',
				),
			);
			$tokens = array_merge( $tokens, $fields );
		}

		return $tokens;
	}

	/**
	 * @param $value
	 * @param $pieces
	 * @param $recipe_id
	 * @param $trigger_data
	 *
	 * @return mixed
	 */
	public function parse_peepso_pro_token( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args ) {

		if ( empty( $pieces ) ) {
			return $value;
		}

		if ( $pieces ) {

			if ( in_array( 'POSTID', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'POSTBODY', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'POSTURL', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'AVATARURL', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'FL_USERNAME', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'FL_FIRST_NAME', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'FL_LAST_NAME', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'FL_GENDER', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'FL_BIRTHDATE', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'FL_FOLLOWERS', $pieces, true ) ) {
				return ( $this->get_token_value( $pieces ) ) ? $this->get_token_value( $pieces ) : 0;
			}

			if ( in_array( 'FL_FOLLOWING', $pieces, true ) ) {
				return ( $this->get_token_value( $pieces ) ) ? $this->get_token_value( $pieces ) : 0;
			}

			if ( in_array( 'FL_PROFILEURL', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'FL_EMAIL', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'FL_ABOUTME', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'FL_WEBSITE', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'FL_ROLE', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'FL_USERROLE', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}
			if ( in_array( 'USR_AVATARURL', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'USR_GENDER', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'USR_BIRTHDATE', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'USR_FOLLOWERS', $pieces, true ) ) {
				return ( $this->get_token_value( $pieces ) ) ? $this->get_token_value( $pieces ) : 0;
			}

			if ( in_array( 'USR_FOLLOWING', $pieces, true ) ) {
				return ( $this->get_token_value( $pieces ) ) ? $this->get_token_value( $pieces ) : 0;
			}

			if ( in_array( 'USR_PROFILEURL', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'USR_ABOUTME', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'USR_WEBSITE', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'USR_USERROLE', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'PPFIELD_NAME', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}

			if ( in_array( 'PPSPVALUE', $pieces, true ) ) {
				return $this->get_token_value( $pieces );
			}
		}

		return $value;
	}

	/**
	 * @param $pieces
	 *
	 * @return mixed
	 */
	public function get_token_value( $pieces = array() ) {
		$meta_field = $pieces[2];
		$trigger_id = absint( $pieces[0] );
		$meta_value = $this->get_form_data_from_trigger_meta( $meta_field, $trigger_id );

		if ( is_array( $meta_value ) ) {
			$value = join( ', ', $meta_value );
		} else {
			$value = $meta_value;
		}

		return $value;
	}

	/**
	 * @param $meta_key
	 * @param $trigger_id
	 *
	 * @return mixed|string
	 */
	public function get_form_data_from_trigger_meta( $meta_key, $trigger_id ) {
		global $wpdb;
		if ( empty( $meta_key ) || empty( $trigger_id ) ) {
			return '';
		}

		$meta_value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}uap_trigger_log_meta WHERE meta_key = %s AND automator_trigger_id = %d ORDER BY ID DESC LIMIT 0,1", $meta_key, $trigger_id ) );
		if ( ! empty( $meta_value ) ) {
			return maybe_unserialize( $meta_value );
		}

		return '';
	}

}
