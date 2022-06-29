<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_USER_HAS_ROLE
 *
 * @package Uncanny_Automator_Pro
 */
class WP_USER_HAS_ROLE extends Action_Condition {

	/**
	 * Method define_condition
	 *
	 * @return void
	 */
	public function define_condition() {

		$this->integration  = 'WP';
		$this->name         = __( 'The user has {{a specific}} role', 'uncanny-automator-pro' );
		$this->code         = 'USER_HAS_ROLE';
		$this->dynamic_name = sprintf(
			/* translators: the role name */
			esc_html__( 'The user has {{a specific:%1$s}} role', 'uncanny-automator-pro' ),
			'ROLE'
		);
		$this->is_pro        = true;
		$this->requires_user = true;
		$this->deprecated    = false;
	}

	/**
	 * Method fields
	 *
	 * @return array
	 */
	public function fields() {

		$wp_roles = wp_roles()->roles;

		$role_options = array();

		foreach ( $wp_roles as $role_name => $role_info ) {
			$role_options[] = array(
				'value' => $role_name,
				'text'  => $role_info['name'],
			);
		}

		return array(
			$this->field->select_field_args(
				array(
					'option_code'           => 'ROLE',
					'label'                 => esc_html__( 'Role', 'uncanny-automator-pro' ),
					'required'              => true,
					'options'               => $role_options,
					'supports_custom_value' => false,
				)
			),
		);
	}

	/**
	 * Evaluate_condition
	 *
	 * Has to use the $this->condition_failed( $message ); method if the condition is not met.
	 *
	 * @return void
	 */
	public function evaluate_condition() {

		$role = $this->get_parsed_option( 'ROLE' );

		$user = get_userdata( $this->user_id );

		if ( empty( $user ) ) {
			throw new \Exception( __( 'User data was not found', 'uncanny-automator-pro' ) );
		}

		$condition_met = in_array( $role, $user->roles, true );

		// If the conditions is not met, send an error message and mark the condition as failed.
		if ( false === $condition_met ) {

			$message = $this->generate_error_message();

			$this->condition_failed( $message );

		}

		// If the condition is met, do nothing.

	}

	/**
	 * Generate_error_message
	 *
	 * @return string
	 */
	public function generate_error_message() {

		$readable_role = $this->get_option( 'ROLE_readable' );

		return __( "User doesn't have the required role: ", 'uncanny-automator-pro' ) . $readable_role;
	}
}
