<?php

namespace Uncanny_Automator_Pro;

/**
 * Class PMP_REMOVEUSERTOMEMBERSHIP
 *
 * @package Uncanny_Automator_Pro
 */
class PMP_REMOVEUSERTOMEMBERSHIP {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'PMP';

	private $action_code;

	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {

		$this->action_code = 'PMPREMOVEMEMBERSHIPLEVEL';

		$this->action_meta = 'REMOVEUSERFROMMEMBERSHIPLEVEL';

		$this->define_action();

	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		$options = Automator()->helpers->recipe->paid_memberships_pro->options->all_memberships( esc_attr__( 'Membership level', 'uncanny-automator-pro' ), $this->action_meta );

		$options['options']['-1'] = esc_attr__( 'All memberships', 'uncanny-automator' );

		$action = array(
			'author'             => Automator()->get_author_name(),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'integration/paid-memberships-pro/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - Paid Memberships Pro */
			'sentence'           => sprintf( esc_attr__( 'Remove the user from {{a membership level:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - Paid Memberships Pro */
			'select_option_name' => esc_attr__( 'Remove the user from {{a membership level}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'remove_user_from_membership_level' ),
			'options'            => array(
				$options,
			),
		);

		Automator()->register->action( $action );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function remove_user_from_membership_level( $user_id, $action_data, $recipe_id, $args ) {

		$membership_level = $action_data['meta'][ $this->action_meta ];

		$user_membership_levels = $this->get_user_membership_levels( $user_id );

		// Do this for 'Any' selection.
		if ( intval( '-1' ) === intval( $membership_level ) ) {

			// Check if user has any membership levels first. Complete with error if does not have.
			if ( empty( $user_membership_levels ) ) {
				$action_data['do-nothing']           = true;
				$action_data['complete_with_errors'] = true;
				Automator()->complete_action( $user_id, $action_data, $recipe_id, sprintf( __( 'User does not belong to any membership levels.', 'uncanny-automator-pro' ) ) );
				return;
			}

			// Delete all membership leverls.
			foreach ( $user_membership_levels as $membership_level ) {
				$cancel_level = pmpro_cancelMembershipLevel( absint( $membership_level ), absint( $user_id ) );
			}

			Automator()->complete_action( $user_id, $action_data, $recipe_id );

			return;

		}

		// Otherwise, remove specific membership level.
		if ( ! in_array( $membership_level, $user_membership_levels, true ) ) {
			// Complete with error if the user was not a member of the specified level.
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;

			Automator()->complete_action( $user_id, $action_data, $recipe_id, sprintf( __( 'User was not a member of the specified level.', 'uncanny-automator-pro' ) ) );

			return;

		}

		// Try removing user membership level.
		if ( pmpro_cancelMembershipLevel( absint( $membership_level ), absint( $user_id ) ) ) {

			Automator()->complete_action( $user_id, $action_data, $recipe_id );

			return;

		}

		$action_data['do-nothing'] = true;

		$action_data['complete_with_errors'] = true;

		Automator()->complete_action( $user_id, $action_data, $recipe_id, sprintf( __( "We're unable to cancel the specified level from the user.", 'uncanny-automator-pro' ) ) );

	}

	/**
	 * Get the user membership levels. This function supports multiple membership.
	 *
	 * @param int @user_id The user id.
	 *
	 * @return Array The collection of membership levels by ID.
	 */
	protected function get_user_membership_levels( $user_id = 0 ) {

		if ( ! function_exists( 'pmpro_getMembershipLevelsForUser' ) ) {
			return array();
		}

		$user_membership_levels = pmpro_getMembershipLevelsForUser( $user_id );

		// Convert result into simple array.
		return array_map(
			function( $membership_level ) {
				return $membership_level->ID;
			},
			$user_membership_levels
		);

	}

}
