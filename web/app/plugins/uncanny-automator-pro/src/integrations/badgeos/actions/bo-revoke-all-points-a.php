<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BO_REVOKE_ALL_POINTS_A
 * @package Uncanny_Automator_Pro
 */
class BO_REVOKE_ALL_POINTS_A {
	/**
	 * integration code
	 * @var string
	 */

	public static $integration = 'BO';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'BOREVOKEALLPOINTS';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/badgeos/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Actions - BadgeOS */
			'sentence'           => sprintf( __( 'Revoke all {{of a certain type of:%1$s}} points from the user', 'uncanny-automator-pro' ), 'BOPOINTSTYPE' ),
			/* translators: Actions - BadgeOS */
			'select_option_name' => __( 'Revoke all {{of a certain type of}} points from the user', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'revoke_bo_points' ],
			'options'            => [],
			'options_group'      => [
				'BOPOINTSTYPE' => [
					$uncanny_automator->helpers->recipe->badgeos->options->list_bo_points_types( __( 'Point type', 'uncanny-automator-pro' ), 'BOPOINTSTYPE', [
						'token'       => false,
						'is_ajax'     => false,
						'include_all' => true,

					] ),
				],
			],
		];

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function revoke_bo_points( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$points_type = $action_data['meta']['BOPOINTSTYPE'];

		$credit_types = badgeos_get_point_types();

		if ( 'ua-all-bo-types' === $points_type ) {
			if ( is_array( $credit_types ) && ! empty( $credit_types ) ) {
				foreach ( $credit_types as $point_type ) {
					$this->revokePoints( $point_type->ID, $user_id );
				}
				$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
			}
		} elseif ( 'ua-all-bo-types' !== $points_type ) {
			if ( is_array( $credit_types ) && ! empty( $credit_types ) ) {
				foreach ( $credit_types as $point_type ) {
					if ( $point_type->post_name === $points_type ) {
						$this->revokePoints( $point_type->ID, $user_id );
						break;
					}
				}
				$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
			}
		} else {
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, __( "The user didn't have the specified achievement.", 'uncanny-automator-pro' ) );
		}

	}

	private function revokePoints( $credit_id, $user_id ) {
		$deduct_points = badgeos_get_points_by_type( $credit_id, absint( $user_id ) );
		badgeos_revoke_credit( $credit_id, absint( $user_id ), 'Deduct', absint( $deduct_points ), '', false, '', '' );
	}
}
