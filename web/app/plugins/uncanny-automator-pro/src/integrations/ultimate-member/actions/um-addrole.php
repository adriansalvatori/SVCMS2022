<?php

namespace Uncanny_Automator_Pro;

/**
 * Class UM_ADDROLE
 *
 * @package Uncanny_Automator_Pro
 */
class UM_ADDROLE {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'UM';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'UMADDROLE';
		$this->action_meta = 'UMROLE';
		if ( Automator()->helpers->recipe->is_edit_page() ) {
			add_action(
				'wp_loaded',
				function () {
					$this->define_action();
				},
				99
			);

			return;
		}
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code ),
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'is_pro'             => true,
			/* translators: Action - Ultimate Member */
			'sentence'           => sprintf( esc_attr__( "Add {{a role:%1\$s}} to the user's roles", 'uncanny-automator' ), $this->action_meta ),
			/* translators: Action - Ultimate Member */
			'select_option_name' => esc_attr__( "Add {{a role}} to the user's roles", 'uncanny-automator' ),
			'priority'           => 11,
			'accepted_args'      => 3,
			'execution_function' => array( $this, 'add_um_role' ),
			'options'            => array(
				$uncanny_automator->helpers->recipe->wp->options->wp_user_roles( null, $this->action_meta ),
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
	public function add_um_role( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$role = $action_data['meta'][ $this->action_meta ];

		$user_obj = new \WP_User( (int) $user_id );
		if ( $user_obj instanceof \WP_User ) {
			$user_obj->add_role( $role );
			$uncanny_automator->complete->user->action( $user_id, $action_data, $recipe_id );
		}
	}

}
