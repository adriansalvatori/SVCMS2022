<?php

namespace Uncanny_Automator_Pro;

Use Uncanny_Automator\Restrict_Content_Tokens;

/**
 * Class Restrict_Content_Pro_Tokens
 * @package Uncanny_Automator_Pro
 */
class Restrict_Content_Pro_Tokens extends Restrict_Content_Tokens {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'RC';

	public function __construct() {
		add_filter( 'automator_maybe_trigger_rc_rcmembershiplevelcancel_tokens', [ $this, 'possible_tokens_cancel' ], 9999, 2 );
		add_filter( 'automator_maybe_trigger_rc_rcmembershiplevelexpired_tokens', [ $this, 'possible_tokens_expired' ], 9999, 2 );
		add_filter( 'automator_maybe_parse_token', [ $this, 'rc_token_cancel' ], 20, 6 );
		add_filter( 'automator_maybe_parse_token', [ $this, 'rc_token_expire' ], 20, 6 );
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function possible_tokens_cancel( $tokens = [], $args = [] ) {

		if ( ! isset( $args['value'] ) || ! isset( $args['meta'] ) ) {
			return $tokens;
		}

		if ( empty( $args['value'] ) || empty( $args['meta'] ) ) {
			return $tokens;
		}

		$id           = $args['value'];

		$new_tokens = [];
		if ( ! empty( $id ) && absint( $id ) ) {
			$new_tokens[] = [
				'tokenId'         => 'RCMEMBERSHIPLEVEL_INITIAL',
				'tokenName'       => _x( 'Membership initial payment', 'Restrict Content', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => 'RCMEMBERSHIPCANCELLED',
			];
			$new_tokens[] = [
				'tokenId'         => 'RCMEMBERSHIPLEVEL_RECURRING',
				'tokenName'       => _x( 'Membership recurring payment', 'Restrict Content', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => 'RCMEMBERSHIPCANCELLED',
			];

			$tokens = array_merge( $tokens, $new_tokens );
		}

		return $tokens;
	}

	/**
	 * @param array $tokens
	 * @param array $args
	 *
	 * @return array
	 */
	public function possible_tokens_expired( $tokens = [], $args = [] ) {

		if ( ! isset( $args['value'] ) || ! isset( $args['meta'] ) ) {
			return $tokens;
		}

		if ( empty( $args['value'] ) || empty( $args['meta'] ) ) {
			return $tokens;
		}

		$id           = $args['value'];

		$new_tokens = [];
		if ( ! empty( $id ) && absint( $id ) ) {
			$new_tokens[] = [
				'tokenId'         => 'RCMEMBERSHIPLEVEL_INITIAL',
				'tokenName'       => _x( 'Membership initial payment', 'Restrict Content', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => 'RCMEMBERSHIPEXPIRED',
			];
			$new_tokens[] = [
				'tokenId'         => 'RCMEMBERSHIPLEVEL_RECURRING',
				'tokenName'       => _x( 'Membership recurring payment', 'Restrict Content', 'uncanny-automator' ),
				'tokenType'       => 'text',
				'tokenIdentifier' => 'RCMEMBERSHIPEXPIRED',
			];

			$tokens = array_merge( $tokens, $new_tokens );
		}

		return $tokens;
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
	public function rc_token_cancel( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args = [] ) {

		if ( $pieces ) {
			if ( in_array( 'RCMEMBERSHIPCANCELLED', $pieces ) ) {
				if ( ! absint( $user_id ) ) {
					return $value;
				}

				if ( ! absint( $recipe_id ) ) {
					return $value;
				}

				global $uncanny_automator;
				$replace_pieces = $replace_args['pieces'];
				$trigger_log_id = $replace_args['trigger_log_id'];
				$run_number     = $replace_args['run_number'];
				$user_id        = $replace_args['user_id'];
				$trigger_id     = absint( $replace_pieces[0] );

				$membership_id = $uncanny_automator->get->get_trigger_log_meta(
					'RCMEMBERSHIPLEVELCANCEL_MEMBERSHIPID',
					$trigger_id,
					$trigger_log_id,
					$run_number,
					$user_id
				);

				if ( $membership_id ) {
					$membership = rcp_get_membership( $membership_id );
					if ( false !== $membership ) {
						switch ( $pieces[2] ) {
							case 'RCMEMBERSHIPLEVELCANCEL':
								return $membership->get_membership_level_name();
								break;
							case 'RCMEMBERSHIPLEVEL_INITIAL':
								return $membership->get_initial_amount();
								break;
							case 'RCMEMBERSHIPLEVEL_RECURRING':
								return $membership->get_recurring_amount();
								break;
						}
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
	public function rc_token_expire( $value, $pieces, $recipe_id, $trigger_data, $user_id, $replace_args = [] ) {

		if ( $pieces ) {
			if ( in_array( 'RCMEMBERSHIPEXPIRED', $pieces ) ) {
				if ( ! absint( $user_id ) ) {
					return $value;
				}

				if ( ! absint( $recipe_id ) ) {
					return $value;
				}

				global $uncanny_automator;
				$replace_pieces = $replace_args['pieces'];
				$trigger_log_id = $replace_args['trigger_log_id'];
				$run_number     = $replace_args['run_number'];
				$user_id        = $replace_args['user_id'];
				$trigger_id     = absint( $replace_pieces[0] );

				$membership_id = $uncanny_automator->get->get_trigger_log_meta(
					'RCMEMBERSHIPLEVELEXPIRED_MEMBERSHIPID',
					$trigger_id,
					$trigger_log_id,
					$run_number,
					$user_id
				);

				if ( $membership_id ) {
					$membership = rcp_get_membership( $membership_id );
					if ( false !== $membership ) {
						switch ( $pieces[2] ) {
							case 'RCMEMBERSHIPLEVELEXPIRED':
								return $membership->get_membership_level_name();
								break;
							case 'RCMEMBERSHIPLEVEL_INITIAL':
								return $membership->get_initial_amount();
								break;
							case 'RCMEMBERSHIPLEVEL_RECURRING':
								return $membership->get_recurring_amount();
								break;
						}
					}
				}
			}
		}

		return $value;
	}
}