<?php

namespace Uncanny_Automator_Pro;

/**
 * Class GP_REVOKEPOINTS_A
 *
 * @package Uncanny_Automator_Pro
 */
class GP_REVOKEPOINTS_A {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'GP';

	private $action_code;
	private $action_meta;
	private $quiz_list;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'GPREVOKEPOINTS';
		$this->action_meta = 'GPPOINTS';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/gamipress/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Actions - GamiPress */
			'sentence'           => sprintf( __( 'Revoke {{a number:%1$s}} {{of a specific type of:%2$s}} points from the user', 'uncanny-automator-pro' ), 'GPPOINTVALUE', $this->action_meta ),
			/* translators: Actions - GamiPress */
			'select_option_name' => __( 'Revoke {{points}} from the user', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'revoke_points' ),
			'options'            => array(),
			'options_group'      => array(
				$this->action_meta => array(
					$uncanny_automator->helpers->recipe->gamipress->options->list_gp_points_types(
						__( 'Point type', 'uncanny-automator' ),
						$this->action_meta,
						array(
							'token'   => false,
							'is_ajax' => false,

						)
					),
				),
				'GPPOINTVALUE'     => array(
					$uncanny_automator->helpers->recipe->field->integer_field( 'GPPOINTVALUE', __( 'Points', 'uncanny-automator' ), false, '0' ),
				),
			),
		);

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

		$deduct_points = 0;

		$points = Automator()->parse->text( $action_data['meta']['GPPOINTVALUE'], $recipe_id, $user_id, $args );

		$points_type = $action_data['meta'][ $this->action_meta ];

		$existing_points = gamipress_get_user_points( absint( $user_id ), $points_type );

		if ( ( $existing_points - absint( $points ) ) < 0 ) {

			$deduct_points = absint( $points ) + ( $existing_points - absint( $points ) );

		} else {

			$deduct_points = absint( $points );

		}

		gamipress_deduct_points_to_user( absint( $user_id ), absint( $deduct_points ), $points_type );

		Automator()->complete_action( $user_id, $action_data, $recipe_id );

	}

}
