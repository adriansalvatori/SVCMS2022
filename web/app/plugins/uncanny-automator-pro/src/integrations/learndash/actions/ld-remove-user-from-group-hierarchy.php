<?php

namespace Uncanny_Automator_Pro;

/**
 * Class LD_REMOVE_USER_FROM_GROUP_HIERARCHY
 *
 * @package Uncanny_Automator_Pro
 */
class LD_REMOVE_USER_FROM_GROUP_HIERARCHY {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'LD';
	/**
	 * @var string
	 */
	private $action_code;
	/**
	 * @var string
	 */
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'REMOVE_USER_FROM_GROUP_HIERARCHY';
		$this->action_meta = 'LDGROUP';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/learndash/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - LearnDash */
			'sentence'           => sprintf( __( 'Remove the user from {{a group:%1$s}} and its children', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - LearnDash */
			'select_option_name' => __( 'Remove the user from {{a group}} and its children', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'remove_from_group' ),
			'options'            => array(
				$uncanny_automator->helpers->recipe->learndash->options->pro->all_ld_groups_with_hierarchy( __( 'Group', 'uncanny-automator' ), $this->action_meta, false ),
			),
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Validation function when the action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 */
	public function remove_from_group( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		if ( ! Learndash_Pro_Helpers::is_group_hierarchy_enabled() ) {
			$error_message                       = esc_attr__( 'The LearnDash Group hierarchy setting is not enabled.', 'uncanny-automator-pro' );
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}
		$group_id             = Automator()->parse->text( $action_data['meta'][ $this->action_meta ], $recipe_id, $user_id, $args );
		$all_hierarchy_groups = Learndash_Pro_Helpers::get_group_children_in_an_action( $group_id, 1, array() );
		array_push( $all_hierarchy_groups, $group_id );
		$all_current_user_groups = learndash_get_users_group_ids( $user_id, true );
		$common                  = array_intersect( $all_hierarchy_groups, $all_current_user_groups );
		if ( ! $common ) {
			$error_message                       = esc_attr__( 'The user does not belong to any of the groups in the hierarchy.', 'uncanny-automator-pro' );
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}
		foreach ( $common as $group_id ) {
			ld_update_group_access( $user_id, $group_id, true );
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
