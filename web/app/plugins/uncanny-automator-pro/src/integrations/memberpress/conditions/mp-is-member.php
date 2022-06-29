<?php

namespace Uncanny_Automator_Pro;

/**
 * Class MP_IS_MEMBER
 *
 * @package Uncanny_Automator_Pro
 */
class MP_IS_MEMBER extends Action_Condition {

	/**
	 * Method define_condition
	 *
	 * @return void
	 */
	public function define_condition() {

		$this->integration = 'MP';
		/* translators: Token */
		$this->name         = __( 'The user is an active member of {{a membership}}', 'uncanny-automator-pro' );
		$this->code         = 'IS_MEMBER';
		$this->dynamic_name = sprintf(
			/* translators: A token matches a value */
			esc_html__( 'The user is an active member of {{a membership:%1$s}}', 'uncanny-automator-pro' ),
			'MEMBERSHIP'
		);
		$this->is_pro        = true;
		$this->requires_user = true;
	}

	/**
	 * Method fields
	 *
	 * @return array
	 */
	public function fields() {

		$memberships_field_args = array(
			'option_code'           => 'MEMBERSHIP',
			'label'                 => esc_html__( 'Membership', 'uncanny-automator-pro' ),
			'required'              => true,
			'options'               => $this->get_memberships_options(),
			'supports_custom_value' => false,
		);

		return array(
			$this->field->select_field_args( $memberships_field_args ),
		);
	}

	/**
	 * Method get_memberships_options
	 *
	 * @return array
	 */
	public function get_memberships_options() {

		$memberships = \MeprCptModel::all( 'MeprProduct' );

		$options = array();

		foreach ( $memberships as $membership ) {
			$options[] = array(
				'value' => $membership->ID,
				'text'  => $membership->post_title,
			);
		}

		return $options;
	}

	/**
	 * Method evaluate_condition
	 *
	 * Has to use the $this->condition_failed( $message ); method if the condition is not met.
	 *
	 * @return void
	 */
	public function evaluate_condition() {

		$membership_id = $this->get_parsed_option( 'MEMBERSHIP' );

		$mepr_user = new \MeprUser( $this->user_id );

		$is_member = $mepr_user->is_already_subscribed_to( $membership_id );

		if ( false === $is_member ) {

			$message = __( 'User is not a member of ', 'uncanny-automator-pro' );

			$message .= $this->get_option( 'MEMBERSHIP_readable' );

			$this->condition_failed( $message );

		}

	}

}
