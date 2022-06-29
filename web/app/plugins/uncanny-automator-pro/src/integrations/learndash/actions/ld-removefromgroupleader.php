<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Recipe;

/**
 * Class LD_REMOVEFROMGROUPLEADER
 *
 * @package Uncanny_Automator_Pro
 */
class LD_REMOVEFROMGROUPLEADER {
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
		$this->set_action_code( 'REMOVEGROUPLEADER_CODE' );
		$this->set_action_meta( 'REMOVEGROUPLEADER_META' );
		$this->set_support_link( $this->get_action_code(), 'integration/learndash/' );
		$this->set_requires_user( true );
		$this->set_is_pro( true );
		/* translators: Action - LearnDash */
		$this->set_sentence( sprintf( esc_attr__( 'Remove the user as a leader of {{a group:%1$s}}', 'uncanny-automator-pro' ), $this->get_action_meta() ) );
		/* translators: Action - LearnDash */
		$this->set_readable_sentence( esc_attr__( 'Remove the user as a leader of {{a group}}', 'uncanny-automator-pro' ) );
		$this->set_options(
			array(
				Automator()->helpers->recipe->learndash->options->pro->all_ld_groups( __( 'Group', 'uncanny-automator' ), $this->get_action_meta(), true ),
			)
		);
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
		$group_id = Automator()->parse->text( $action_data['meta'][ $this->action_meta ], $recipe_id, $user_id, $args );

		if ( intval( '-1' ) !== intval( $group_id ) ) {
			$check_group = learndash_validate_groups( array( $group_id ) );
			if ( empty( $check_group ) || ! is_array( $check_group ) ) {
				$action_data['do-nothing']           = true;
				$action_data['complete_with_errors'] = true;
				$error_message                       = esc_html__( 'The selected group is not found.', 'uncanny-automator-pro' );
				Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

				return;
			}
		}

		$all_groups_list = learndash_get_administrators_group_ids( $user_id, true );
		if ( empty( $all_groups_list ) ) {
			$action_data['complete_with_errors'] = true;
			$action_data['do-nothing']           = true;
			$error_message                       = esc_html__( 'The user is not a Group Leader of any group.', 'uncanny-automator-pro' );
			Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$common_groups = array_intersect( array( $group_id ), $all_groups_list );
		if ( intval( '-1' ) === intval( $group_id ) ) {
			$common_groups = $all_groups_list;
		}
		foreach ( $common_groups as $common_group_id ) {
			//Remove leader from all groups
			ld_update_leader_group_access( $user_id, $common_group_id, true );
		}

		Automator()->complete->action( $user_id, $action_data, $recipe_id );

	}
}
