<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Recipe;

/**
 * Class LD_REMOVEFROMALLGROUPS
 *
 * @package Uncanny_Automator_Pro
 */
class LD_REMOVEFROMALLGROUPS {
	use Recipe\Actions;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->setup_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	protected function setup_action() {
		$this->set_integration( 'LD' );
		$this->set_action_code( 'REMOVEGROUPS_CODE' );
		$this->set_action_meta( 'REMOVEGROUPS_META' );
		$this->set_support_link( $this->get_action_code(), 'integration/learndash/' );
		$this->set_requires_user( true );
		$this->set_is_pro( true );
		/* translators: Action - LearnDash */
		$this->set_sentence( esc_attr__( 'Remove the user from all groups', 'uncanny-automator-pro' ) );
		/* translators: Action - LearnDash */
		$this->set_readable_sentence( esc_attr__( 'Remove the user from all groups', 'uncanny-automator-pro' ) );
		$this->set_options( array() );
		$this->register_action();
	}

	/**
	 * Process the action.
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 * @param $parsed
	 *
	 * @return void.
	 */
	protected function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {
		$user_groups = learndash_get_users_group_ids( $user_id, true );

		if ( empty( $user_groups ) ) {
			$action_data['complete_with_errors'] = true;
			$error_message                       = esc_html__( 'The user does not belong to any of the groups.', 'uncanny-automator-pro' );
			Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		foreach ( $user_groups as $group_id ) {
			//Remove from all groups
			ld_update_group_access( $user_id, $group_id, true );
		}

		Automator()->complete->action( $user_id, $action_data, $recipe_id );

	}
}
