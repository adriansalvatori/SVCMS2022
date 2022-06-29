<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_REMOVEROLE
 * @package Uncanny_Automator_Pro
 */
class WP_REMOVEROLE {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'WPREMOVEROLE';
		$this->action_meta = 'WPROLE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;
		
		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/wordpress-core/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - WordPress Core */
			'sentence'           => sprintf( __( "Remove {{a role:%1\$s}} from the user's roles", 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - WordPress Core */
			'select_option_name' => __( 'Remove {{a role}} from the user', 'uncanny-automator-pro' ),
			'priority'           => 11,
			'accepted_args'      => 3,
			'execution_function' => array( $this, 'remove_user_role' ),
			'options_callback'	  => array( $this, 'load_options' ),
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * load_options
	 *
	 * @return void
	 */
	public function load_options() {

		global $wp_roles;
		$roles = [];
		if ( ! empty( $wp_roles ) ) {
			foreach ( $wp_roles->roles as $key => $role ) {
				$roles[ $key ] = $role['name'];
			}
		}

		$options = Automator()->utilities->keep_order_of_options(
				array(
					'options'            => array(
						Automator()->helpers->recipe->field->select_field( $this->action_meta, __( 'Select a role', 'uncanny-automator' ), $roles ),
					),
			)
		);
		
		return $options;
	}

	/**
	 * Validation function when the action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function remove_user_role( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;
		$role = $action_data['meta'][ $this->action_meta ];

		$user_obj   = new \WP_User( (int) $user_id );
		$user_roles = $user_obj->roles;

		if ( ! in_array( $role, $user_roles ) ) {
			$error_msg                           = sprintf( __( 'User did not have the specified (%1$s) role. No action taken.', 'uncanny-automator-pro' ), $role );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_msg );

			return;
		} elseif ( in_array( $role, $user_roles ) && count( $user_roles ) == 1 ) {
			$error_msg                           = sprintf( __( 'Specified (%1$s) role could not be removed because it was the only role assigned to the user.', 'uncanny-automator-pro' ), $role );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_msg );

			return;
		}

		if ( ! in_array( 'administrator', $user_roles ) ) {
			$user_obj->remove_role( $role );
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
		} else {
			$error_message                       = __( 'For security, the remove role action cannot be applied to Administrators.', 'uncanny-automator-pro' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );
		}
	}
}
