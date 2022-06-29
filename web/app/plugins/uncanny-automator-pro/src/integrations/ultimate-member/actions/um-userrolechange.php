<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_SETPOSTMETA
 *
 * @package Uncanny_Automator_Pro
 */
class UM_USERROLECHANGE {

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
		$this->action_code = 'UMUSERROLECHANGE';
		$this->action_meta = 'WPROLE';
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

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {
		global $uncanny_automator;
		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code, 'integration/ultimate-member/' ),
			'is_pro'             => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - Ultimate Member */
			'sentence'           => sprintf( __( "Set the user's role to {{a specific role:%1\$s}}", 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - Ultimate Member */
			'select_option_name' => __( "Set the user's role to {{a specific role}}", 'uncanny-automator-pro' ),
			'priority'           => 11,
			'accepted_args'      => 3,
			'execution_function' => array( $this, 'user_role' ),
			'options'            => array(
				$uncanny_automator->helpers->recipe->wp->options->wp_user_roles(),
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
	public function user_role( $user_id, $action_data, $recipe_id, $args ) {

		global $uncanny_automator;

		$role = $action_data['meta'][ $this->action_meta ];

		$user_obj = get_user_by( 'ID', (int) $user_id );
		if ( ! is_wp_error( $user_obj ) && ! user_can( $user_obj, 'administrator' ) ) {
			$user_obj->set_role( $role );
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
		} else {
			$recipe_log_id                       = $action_data['recipe_log_id'];
			$args['do-nothing']                  = true;
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$error_message                       = __( 'For security, the change role action cannot be applied to administrators.', 'uncanny-automator-pro' );
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message, $recipe_log_id, $args );
		}
	}

}
