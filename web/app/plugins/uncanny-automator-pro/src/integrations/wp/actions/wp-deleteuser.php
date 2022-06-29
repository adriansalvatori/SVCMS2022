<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_DELETEUSER
 *
 * @package Uncanny_Automator_Pro
 */
class WP_DELETEUSER {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'WP';

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
		$this->action_code = 'DELETEUSER';
		$this->action_meta = 'WPUSER';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		$action = array(
			'author'             => Automator()->get_author_name( $this->action_code ),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'integration/wordpress-core/' ),
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'requires_user'      => false,
			'is_pro'             => true,
			/* translators: Action - WordPress */
			'sentence'           => sprintf( esc_attr__( 'Delete {{a user:%1$s}}', 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - WordPress */
			'select_option_name' => esc_attr__( 'Delete {{a user}}', 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'delete_user' ),
			'options_callback'	  => array( $this, 'load_options' ),
			// very last call in WP, we need to make sure they viewed the page and didn't skip before is was fully viewable
			
		);

		Automator()->register->action( $action );
	}

	/**
	 * load_options
	 *
	 * @return void
	 */
	public function load_options() {

		$options = array(
			'-1'     => esc_attr__( 'Any', 'uncanny-automator-pro' ),
			'ID'     => esc_attr__( 'User ID', 'uncanny-automator-pro' ),
			'login'  => esc_attr__( 'Username', 'uncanny-automator-pro' ),
			'email'  => esc_attr__( 'User email', 'uncanny-automator-pro' ),
			'domain' => esc_attr__( 'Domain', 'uncanny-automator-pro' ),
		);

		$options = Automator()->utilities->keep_order_of_options(
			array(
				'options_group'      => array(
					$this->action_meta => array(
						Automator()->helpers->recipe->field->select_field( 'SEARCHFIELD', esc_attr__( 'Select user by', 'uncanny-automator-pro' ), $options, '', '', true, esc_attr__( 'Only alphanumeric, _, space, ., -, @', 'uncanny-automator-pro' ) ),
						Automator()->helpers->recipe->field->text_field( $this->action_meta, esc_attr__( 'Matching', 'uncanny-automator-pro' ), true, 'text', '', true, esc_attr__( 'Only alphanumeric, _, space, ., -, @', 'uncanny-automator-pro' ) ),
						Automator()->helpers->recipe->field->text_field( 'DELETECHECK', esc_attr__( 'I understand that this action will delete the user and all of their data with no way to recover it.  All content owned by the user will also be deleted.', 'uncanny-automator-pro' ), true, 'checkbox', '', true ),
					),
				),
			)
		);
		return $options;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 */
	public function delete_user( $user_id, $action_data, $recipe_id, $args ) {
		$field    = $action_data['meta']['SEARCHFIELD'];
		$value    = Automator()->parse->text( $action_data['meta'][ $this->action_meta ], $recipe_id, $user_id, $args );
		$checkbox = $action_data['meta']['DELETECHECK'];

		if ( $checkbox === false ) {
			return;
		}

		global $wpdb;
		if ( intval( '-1' ) !== intval( $field ) ) {
			if ( 'domain' === (string) $field ) {
				$field = 'email';
			}
			$user = get_user_by( $field, $value );
		} else {
			$user = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE ID= %d OR user_login LIKE %s OR user_email LIKE %s", $value, "%%$value%%", "%%$value%%" ) );
		}

		// user not found. Return error.
		if ( ! $user ) {
			$action_data['complete_with_errors'] = true;
			/* translators: Delete a {{user}} - Error while creating a new user */
			Automator()->complete->action( 0, $action_data, $recipe_id, sprintf( esc_attr__( 'Invalid user details: %1$s', 'uncanny-automator-pro' ), $value ) );

			return;
		}
		if ( is_array( $user->roles ) && in_array( 'administrator', $user->roles, true ) ) {
			$action_data['complete_with_errors'] = true;
			/* translators: Delete a {{user}} - Error while creating a new user */
			Automator()->complete->action( 0, $action_data, $recipe_id, sprintf( esc_attr__( 'Sorry, the selected user is an admin: %1$s', 'uncanny-automator-pro' ), $value ) );

			return;
		}

		require_once ABSPATH . 'wp-admin/includes/user.php';
		wp_delete_user( $user->ID );

		Automator()->complete->action( $user_id, $action_data, $recipe_id );
	}
}
