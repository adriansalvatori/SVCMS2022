<?php

namespace Uncanny_Automator_Pro;

/**
 * Class BDB_SETUSERPROFILETYPE
 * @package Uncanny_Automator_Pro
 */
class BDB_SETUSERPROFILETYPE {

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
		$this->action_code = 'BDBSETUSERPROFILETYPE';
		$this->action_meta = 'BDBPROFILETYPE';

		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object.
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = [
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/buddyboss/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - BuddyBoss */
			'sentence'           => sprintf( __( "Set the user's profile type to {{a specific type:%1\$s}}", 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - BuddyBoss */
			'select_option_name' => __( "Set the user's profile type to {{a specific type}}", 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => [ $this, 'update_user_profile_type' ],
			'options'            => [
				$uncanny_automator->helpers->recipe->buddyboss->pro->get_profile_types( null, $this->action_meta ),
			],
		];

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Update user profile type
	 *
	 * @param string $user_id
	 * @param array $action_data
	 * @param string $recipe_id
	 *
	 * @return void
	 *
	 * @since 1.1
	 */
	public function update_user_profile_type( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		$post_id = $action_data['meta'][ $this->action_meta ];
		if ( empty( $post_id ) ) {
			return;
		}
		// Get post id of selected profile type.
		$member_type = get_post_meta( $post_id, '_bp_member_type_key', true );
		if ( empty( $member_type ) ) {
			$type_post   = get_post( $post_id );
			$member_type = $type_post->post_name;
		}
		$selected_member_type_wp_roles = get_post_meta( $post_id, '_bp_member_type_wp_roles', true );

		// Check if current user is an administrator then just set profile type and add new role but do not remove old role.
		if ( bp_set_member_type( $user_id, $member_type ) ) {
			$bp_current_user = new \WP_User( $user_id );
			// admin check
			$is_admin_role = false;
			foreach ( $bp_current_user->roles as $role ) {
				if ( 'administrator' === $role ) {
					$is_admin_role = true;
					break;
				}
			}
			if ( ! $is_admin_role ) {
				// Remove role
				$bp_current_user->remove_role( $bp_current_user->roles[0] );
			}
			// Add role
			$bp_current_user->add_role( $selected_member_type_wp_roles[0] );
		}

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
