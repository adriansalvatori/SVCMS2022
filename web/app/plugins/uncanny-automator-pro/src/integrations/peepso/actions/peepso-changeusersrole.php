<?php

namespace Uncanny_Automator_Pro;

use PeepSo;
use PeepSoUser;

/**
 * Class PEEPSO_CHANGEUSERSROLE
 *
 * @package Uncanny_Automator
 */
class PEEPSO_CHANGEUSERSROLE {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'PP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'PPCHANGEUSERSROLE';
		$this->action_meta = 'PPUSERSROLE';

		if ( is_admin() ) {
			add_action( 'wp_loaded', array( $this, 'plugins_loaded' ), 99 );
		} else {
			$this->define_action();
		}
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {
		$action = array(
			'author'             => Automator()->get_author_name( $this->action_code ),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'integration/peepso/' ),
			'integration'        => self::$integration,
			'is_pro'             => true,
			'code'               => $this->action_code,
			'requires_user'      => true,
			/* translators: Action - WordPress Core */
			'sentence'           => sprintf( esc_attr__( "Change the user's PeepSo role to {{a new role:%1\$s}}", 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - WordPress Core */
			'select_option_name' => esc_attr__( "Change the user's PeepSo role to {{a new role}}", 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'change_role' ),
			'options_group'      => array(
				$this->action_meta => array(
					Automator()->helpers->recipe->peepso->pro->get_roles( __( 'Roles', 'uncanny-automator-pro' ), $this->action_meta, array( 'uo_include_any' => false ) ),
				),
			),
		);
		Automator()->register->action( $action );
	}

	public function plugins_loaded() {
		$this->define_action();
	}

	/**
	 * Validation function when the action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function change_role( $user_id, $action_data, $recipe_id, $args ) {

		$new_user_role = Automator()->parse->text( $action_data['meta'][ $this->action_meta ], $recipe_id, $user_id, $args );
		$system_roles  = Automator()->helpers->recipe->peepso->pro->get_translated_roles();

		if ( empty( $new_user_role ) || ! isset( $system_roles[ $new_user_role ] ) ) {
			$error_message                       = __( "The selected role doesn't exist.", 'uncanny-automator-pro' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$user = PeepSoUser::get_instance( $user_id );

		if ( 0 === $user_id || null === $user->get_id() ) {
			$error_message                       = __( 'Invalid user.', 'uncanny-automator-pro' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		// Don't allow banning administrators
		if ( PeepSo::is_admin( $user_id ) && 'ban' === $new_user_role ) {
			$error_message                       = __( 'You cannot ban administrators.', 'uncanny-automator-pro' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$user->approve_user();
		// update the user with their new role
		$user->set_user_role( $new_user_role );

		Automator()->complete_action( $user_id, $action_data, $recipe_id );
	}
}
