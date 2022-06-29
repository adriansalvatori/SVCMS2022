<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Recipe\Actions;

/**
 * Class LD_EMAILGROUPSLEADERS_A
 *
 * @package Uncanny_Automator_Pro
 */
class LD_EMAILGROUPSLEADERS_A {
	use Actions;

	/**
	 * Reset key holder
	 *
	 * @var null
	 */
	private $key;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->key_generated = false;
		$this->key           = null;
		$this->setup_action();
	}

	/**
	 * Setting up the action
	 *
	 * @return void
	 */
	protected function setup_action() {

		$this->set_integration( 'LD' );
		$this->set_action_code( 'EMAILGROUPSLEADERS' );
		$this->set_action_meta( 'EMAILTOLEADERS' );
		$this->set_requires_user( true );
		$this->set_is_pro( true );

		/* translators: Action - WordPress */
		$this->set_sentence( sprintf( esc_attr__( "Send an {{email:%1\$s}} to the user's group leader(s)", 'uncanny-automator' ), $this->get_action_meta() ) );

		/* translators: Action - WordPress */
		$this->set_readable_sentence( esc_attr__( "Send an {{email}} to the user's group leader(s)", 'uncanny-automator' ) );

		$options_group = array(
			$this->get_action_meta() => array(
				// Email From Field.
				Automator()->helpers->recipe->field->text(
					array(
						'option_code' => 'EMAILFROM',
						/* translators: Email field */
						'label'       => esc_attr__( 'From', 'uncanny-automator' ),
						'input_type'  => 'email',
						'default'     => '{{admin_email}}',
					)
				),

				// Email From Field.
				Automator()->helpers->recipe->field->text(
					array(
						'option_code' => 'EMAILFROMNAME',
						/* translators: Email field */
						'label'       => esc_attr__( 'From name', 'uncanny-automator' ),
						'input_type'  => 'text',
						'default'     => '{{site_name}}',
					)
				),

				// Email CC field.
				Automator()->helpers->recipe->field->text(
					array(
						'option_code' => 'EMAILCC',
						/* translators: Email field */
						'label'       => esc_attr__( 'CC', 'uncanny-automator' ),
						'input_type'  => 'email',
						'required'    => false,
					)
				),

				// Email BCC field.
				Automator()->helpers->recipe->field->text(
					array(
						'option_code' => 'EMAILBCC',
						/* translators: Email field */
						'label'       => esc_attr__( 'BCC', 'uncanny-automator' ),
						'input_type'  => 'email',
						'required'    => false,
					)
				),

				// Email Subject field.
				Automator()->helpers->recipe->field->text(
					array(
						'option_code' => 'EMAILSUBJECT',
						/* translators: Email field */
						'label'       => esc_attr__( 'Subject', 'uncanny-automator' ),
						'required'    => true,
					)
				),

				// Email Content Field.
				Automator()->helpers->recipe->field->text(
					array(
						'option_code'               => 'EMAILBODY',
						/* translators: Email field */
						'label'                     => esc_attr__( 'Body', 'uncanny-automator' ),
						'input_type'                => 'textarea',
						'supports_fullpage_editing' => true,
					)
				),

			),
		);

		$this->set_options_group( $options_group );

		$this->register_action();

	}

	/**
	 * Handling action function
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 * @param $args
	 * @param $parsed
	 *
	 * @return void.
	 */
	protected function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {
		$body_text = isset( $parsed['EMAILBODY'] ) ? $parsed['EMAILBODY'] : '';

		if ( false !== strpos( $body_text, '{{reset_pass_link}}' ) ) {
			$reset_pass = ! is_null( $this->key ) ? $this->key : Automator()->parse->generate_reset_token( $user_id );
			$body       = str_replace( '{{reset_pass_link}}', $reset_pass, $body_text );
		} else {
			$body = $body_text;
		}
		$ld_group = null;
		// Try to find a single Group ID
		if ( isset( $args['post_id'] ) ) {
			// Post ID found
			$ld_group = absint( $args['post_id'] );
			if ( 'groups' !== get_post_type( $ld_group ) ) {
				//  Nop, not a group post ID
				$ld_group = null;
			}
		}
		// Fallback to grab ALL group IDs
		if ( null === $ld_group ) {
			$ld_group = learndash_get_users_group_ids( $user_id, true );
		}
		// Still empty, no groups found for the user
		if ( empty( $ld_group ) ) {
			$error_message                       = esc_attr__( 'User is not a member of any group.', 'uncanny-automator-pro' );
			$action_data['complete_with_errors'] = true;
			Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}
		$group_leaders = array();
		// If fallback is used to grab all group IDs
		if ( is_array( $ld_group ) ) {
			foreach ( $ld_group as $group_id ) {
				$group_leaders = array_merge( $group_leaders, learndash_get_groups_administrators( $group_id, true ) );
			}
		} else {
			// Single group ID found
			$group_leaders = learndash_get_groups_administrators( $ld_group, true );
		}
		if ( empty( $group_leaders ) ) {
			/* translators: Action - LearnDash Group ID */
			$error_message                       = sprintf( esc_attr__( 'No Group Leader associated with the selected group. ID: %d', 'uncanny-automator-pro' ), $ld_group );
			$action_data['complete_with_errors'] = true;
			Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$to_leaders_emails = array();
		foreach ( $group_leaders as $leader ) {
			$to_leaders_emails[ $leader->ID ] = $leader->user_email;
		}

		$data = array(
			'to'        => implode( ',', $to_leaders_emails ),
			'from'      => isset( $parsed['EMAILFROM'] ) ? $parsed['EMAILFROM'] : '',
			'from_name' => isset( $parsed['EMAILFROMNAME'] ) ? $parsed['EMAILFROMNAME'] : '',
			'cc'        => isset( $parsed['EMAILCC'] ) ? $parsed['EMAILCC'] : '',
			'bcc'       => isset( $parsed['EMAILBCC'] ) ? $parsed['EMAILBCC'] : '',
			'subject'   => isset( $parsed['EMAILSUBJECT'] ) ? $parsed['EMAILSUBJECT'] : '',
			'body'      => $body,
			'content'   => $this->get_content_type(),
			'charset'   => $this->get_charset(),
		);

		$this->set_mail_values( $data );

		$mailed = $this->send_email();

		// Set $this->set_error_message(); and complete the action automatically. May be use return true / false.
		if ( false === $mailed && ! empty( $this->get_error_message() ) ) {

			$error_message                       = $this->get_error_message();
			$action_data['complete_with_errors'] = true;
			Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		Automator()->complete->action( $user_id, $action_data, $recipe_id );

	}
}
