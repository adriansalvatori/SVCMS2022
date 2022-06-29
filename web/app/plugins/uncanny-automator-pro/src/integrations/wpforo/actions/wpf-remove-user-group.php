<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WPF_REMOVE_USER_GROUP
 * @package Uncanny_Automator
 */
class WPF_REMOVE_USER_GROUP {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WPFORO';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'WPFOROREMOVEUSERGROUP';
		$this->action_meta = 'FOROGROUP';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$usergroups = WPF()->usergroup->get_usergroups();

		$group_options = [];
		foreach ( $usergroups as $key => $group ) {
			$group_options[ $group['groupid'] ] = $group['name'];
		}

		$option = [
			'option_code' => 'FOROGROUP',
			'label'       => esc_attr__( 'User groups', 'uncanny-automator' ),
			'input_type'  => 'select',
			'required'    => true,
			'options'     => $group_options,
		];

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/wpforo/' ),
			'integration'        => self::$integration,
			'is_pro'             => true,
			'code'               => $this->action_code,
			/* translators: Action - wpForo */
			'sentence'           => sprintf( esc_attr_x( 'Remove the user from {{a group:%1$s}}', 'wpForo', 'uncanny-automator' ), $this->action_meta ),
			/* translators: Action - wpForo */
			'select_option_name' => esc_attr_x( 'Remove the user from {{a group}}', 'wpForo', 'uncanny-automator' ),
			'priority'           => 10,
			'accepted_args'      => 3,
			'execution_function' => array( $this, 'remove_user_from_group' ),
			'options'            => [
				$option
			],
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
	public function remove_user_from_group( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		$group_id = absint( $action_data['meta'][ $this->action_meta ] );

		$user_group_id = absint( WPF()->member->get_usergroup( $user_id ) );

		if ( ! wpforo_feature( 'role-synch' ) ) {
			if ( $group_id && $group_id === $user_group_id ) {

				$default_group = absint( WPF()->usergroup->default_groupid );
				$sql           = "UPDATE `" . WPF()->tables->profiles . "` SET `groupid` = %d WHERE `userid` = %d";
				if ( false !== WPF()->db->query( WPF()->db->prepare( $sql, $default_group, $user_id ) ) ) {
					WPF()->member->reset( $user_id );
					$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );

					return;
				} else {
					$error_msg = __( 'There was a DB error while removing the user from the group', 'uncanny-automator-pro' );
				}
			} else {
				$error_msg = __( 'User was not a member of the specified group', 'uncanny-automator-pro' );
			}
		} else {
			$error_msg = __( 'User role cannot be set when Role Syncing is on', 'uncanny-automator-pro' );
		}

		$action_data['do-nothing']           = true;
		$action_data['complete_with_errors'] = true;
		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_msg );

		return;
	}
}
