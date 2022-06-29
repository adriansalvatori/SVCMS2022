<?php

namespace Uncanny_Automator_Pro;


/**
 * Class LD_REMOVEGROUP_A
 *
 * @package Uncanny_Automator_Pro
 */
class LD_REMOVEGROUP_A {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'LD';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'REMOVEGROUP_A';
		$this->action_meta = 'LDREMOVEGROUP';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {
		$action = array(
			'author'             => Automator()->get_author_name( $this->action_code ),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'integration/learndash/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - LearnDash */
			'sentence'           => sprintf( esc_attr__( 'Remove the user from {{a group:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - LearnDash */
			'select_option_name' => esc_attr__( 'Remove the user from {{a group}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'remove_from_group' ),
			'options'            => array(
				Automator()->helpers->recipe->learndash->options->all_ld_groups( esc_attr__( 'Group', 'uncanny-automator' ), $this->action_meta, true, false ),
			),
		);

		Automator()->register->action( $action );
	}

	/**
	 * Validation function when the action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function remove_from_group( $user_id, $action_data, $recipe_id, $args ) {
		$group_id = $action_data['meta'][ $this->action_meta ];

		if ( '-1' !== $group_id ) {
			ld_update_group_access( $user_id, $group_id, true );
		} else {
			$all_groups_list = learndash_get_users_group_ids( $user_id );
			foreach ( $all_groups_list as $group_id ) {
				ld_update_group_access( $user_id, $group_id, true );
			}
		}

		Automator()->complete_action( $user_id, $action_data, $recipe_id );
	}
}
