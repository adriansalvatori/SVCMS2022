<?php

namespace Uncanny_Automator_Pro;

class BO_REVOKEPOINTS_A {
	/**
	 * Class BO_REVOKEPOINTS_A
	 * @package Uncanny_Automator_Pro
	 */


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
		$this->action_code = 'BOREVOKEPOINTS';
		$this->action_meta = 'BOPOINTS';
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
			'sentence'           => sprintf( __( 'Revoke {{a number:%1$s}} {{of a specific type of:%2$s}} points from the user', 'uncanny-automator-pro' ), 'BOPOINTVALUE', $this->action_meta ),
			/* translators: Actions - BadgeOS */
			'select_option_name' => __( 'Revoke {{points}} from the user', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'revoke_bo_points' ],
			'options'            => [],
			'options_group'      => [
				$this->action_meta => [
					$uncanny_automator->helpers->recipe->badgeos->options->list_bo_points_types( __( 'Point type', 'uncanny-automator-pro' ), $this->action_meta, [
						'token'   => false,
						'is_ajax' => false,
					] ),
				],
				'BOPOINTVALUE'     => [
					$uncanny_automator->helpers->recipe->field->integer_field( 'BOPOINTVALUE', __( 'Points', 'uncanny-automator-pro' ), false, '0' )
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

		$points_type = $action_data['meta'][ $this->action_meta ];
		$points      = $uncanny_automator->parse->text( $action_data['meta']['BOPOINTVALUE'], $recipe_id, $user_id, $args );

		$point_type_id = 0;
		$deduct_points = 0;
		$credit_types  = badgeos_get_point_types();

		if ( is_array( $credit_types ) && ! empty( $credit_types ) ) {
			foreach ( $credit_types as $point_type ) {
				if ( $point_type->post_name === $points_type ) {
					$point_type_id = $point_type->ID;
					break;
				}
			}
		}

		$existing_points = badgeos_get_points_by_type( $point_type_id, absint( $user_id ) );
		if ( ( $existing_points - absint( $points ) ) < 0 ) {
			$deduct_points = absint( $points ) + ( $existing_points - absint( $points ) );
		} else {
			$deduct_points = absint( $points );
		}

		badgeos_revoke_credit( $point_type_id, absint( $user_id ), 'Deduct', absint( $deduct_points ), '', false, '', '' );

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}