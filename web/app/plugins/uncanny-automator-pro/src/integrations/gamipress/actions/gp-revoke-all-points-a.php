<?php

namespace Uncanny_Automator_Pro;

/**
 * Class GP_REVOKE_ALL_POINTS_A
 * @package Uncanny_Automator_Pro
 */
class GP_REVOKE_ALL_POINTS_A {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'GP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'GPREVOKEALLPOINTS';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/gamipress/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Actions - GamiPress */
			'sentence'           => sprintf( __( 'Revoke all {{of a certain type of:%1$s}} points from the user', 'uncanny-automator-pro' ), 'GPPOINTSTYPE' ),
			/* translators: Actions - GamiPress */
			'select_option_name' => __( 'Revoke all {{of a certain type of}} points from the user', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'revoke_points' ],
			'options'            => [],
			'options_group'      => [
				'GPPOINTSTYPE' => [
					$uncanny_automator->helpers->recipe->gamipress->options->list_gp_points_types( __( 'Point type', 'uncanny-automator-pro' ), 'GPPOINTSTYPE', [
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
	public function revoke_points( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$points_type = $action_data['meta']['GPPOINTSTYPE'];

		if ( 'ua-all-gp-types' === $points_type ) {
			foreach ( gamipress_get_points_types_slugs() as $points_type ) {
				$deduct_points = gamipress_get_user_points( absint( $user_id ), $points_type );
				gamipress_deduct_points_to_user( absint( $user_id ), absint( $deduct_points ), $points_type );
			}
		} else {
			$deduct_points = gamipress_get_user_points( absint( $user_id ), $points_type );
			gamipress_deduct_points_to_user( absint( $user_id ), absint( $deduct_points ), $points_type );
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}

}
