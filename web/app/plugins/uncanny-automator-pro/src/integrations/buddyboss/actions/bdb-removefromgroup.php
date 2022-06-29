<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BDB_REMOVEFROMGROUP
 * @package Uncanny_Automator_Pro
 */
class BDB_REMOVEFROMGROUP {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'BDB';

	private $action_code;
	private $action_meta;

	/**
	 * SetAutomatorTriggers constructor.
	 */
	public function __construct() {
		$this->action_code = 'BDBREMOVEFROMGROUP';
		$this->action_meta = 'BDBREMOVEGROUPS';

		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object.
	 */
	public function define_action() {

		global $uncanny_automator;

		$bp_group_args = [
			'uo_include_any' => true,
			'uo_any_label'   => __( 'All groups', 'uncanny-automator' ),
			'status'         => [ 'public', 'hidden', 'private' ],
		];

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/buddyboss/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - BuddyBoss */
			'sentence'           => sprintf( __( 'Remove user from {{a group:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyBoss */
			'select_option_name' => __( 'Remove user from {{a group}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'remove_from_bp_group' ],
			'options'            => [
				$uncanny_automator->helpers->recipe->buddyboss->options->all_buddyboss_groups( null, $this->action_meta, $bp_group_args ),
			],
		];

		$uncanny_automator->register->action( $action );
	}


	/**
	 * Remove from BP Groups
	 *
	 * @param string $user_id
	 * @param array $action_data
	 * @param string $recipe_id
	 *
	 * @return void
	 *
	 * @since 1.1
	 */
	public function remove_from_bp_group( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		$remove_from_bp_group = $action_data['meta'][ $this->action_meta ];
		if ( $remove_from_bp_group === "-1" ) {
			$all_user_groups = groups_get_user_groups( $user_id );
			if ( ! empty( $all_user_groups['groups'] ) ) {
				foreach ( $all_user_groups['groups'] as $group ) {
					$result = groups_leave_group( $group, $user_id );
				}
			}
		} else {
			groups_leave_group( $remove_from_bp_group, $user_id );
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
