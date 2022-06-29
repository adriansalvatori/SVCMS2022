<?php

namespace Uncanny_Automator_Pro;

/**
 * Class WP_UPDATEUSER
 * @package Uncanny_Automator_Pro
 */
class WP_UPDATEUSER {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WP';

	private $action_code;
	private $action_meta;
	private $key_generated;
	private $key;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code   = 'UPDATEUSER';
		$this->action_meta   = 'USERDETAILS';
		$this->key_generated = false;
		$this->key           = null;
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
			'requires_user'      => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* translators: Action - WordPress */
			'sentence'           => sprintf( __( "Update the user's {{details:%1\$s}}", 'uncanny-automator-pro' ), $this->action_meta ),
			/* translators: Action - WordPress */
			'select_option_name' => __( "Update the user's {{details}}", 'uncanny-automator-pro' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'update_user' ),
			'options_callback'	  => array( $this, 'load_options' ),
			// very last call in WP, we need to make sure they viewed the page and didn't skip before is was fully viewable
			
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * load_options
	 *
	 * @return void
	 */
	public function load_options() {

		$roles                  = Automator()->helpers->recipe->wp->options->wp_user_roles();
		$roles['options']['-1'] = __( 'No role change', 'uncanny-automator-pro' );
		ksort( $roles['options'] );

		$options = Automator()->utilities->keep_order_of_options(
			array(
				'options_group'      => array(
					$this->action_meta => array(
						Automator()->helpers->recipe->field->text_field( 'USERNAME', __( 'Username', 'uncanny-automator-pro' ), true, 'text', '', false, 'Fields left blank will not be updated.' ),
						Automator()->helpers->recipe->field->text_field( 'EMAIL', __( 'Email', 'uncanny-automator-pro' ), true, 'text', '', false, '' ),
						Automator()->helpers->recipe->field->text_field( 'FIRSTNAME', __( 'First name', 'uncanny-automator-pro' ), true, 'text', '', false, '' ),
						Automator()->helpers->recipe->field->text_field( 'LASTNAME', __( 'Last name', 'uncanny-automator-pro' ), true, 'text', '', false, '' ),
						Automator()->helpers->recipe->field->text_field( 'WEBSITE', __( 'Website', 'uncanny-automator-pro' ), true, 'text', '', false, '' ),
						Automator()->helpers->recipe->field->text_field( 'PASSWORD', __( 'Password', 'uncanny-automator-pro' ), true, 'text', '', false, '' ),
						$roles,
						array(
							'input_type'        => 'repeater',
							'option_code'       => 'USERMETA_PAIRS',
							'label'             => __( 'Meta', 'uncanny-automator-pro' ),
							'required'          => false,
							'fields'            => array(
								array(
									'input_type'      => 'text',
									'option_code'     => 'meta_key',
									'label'           => __( 'Key', 'uncanny-automator-pro' ),
									'supports_tokens' => true,
									'required'        => true,
								),
								array(
									'input_type'      => 'text',
									'option_code'     => 'meta_value',
									'label'           => __( 'Value', 'uncanny-automator-pro' ),
									'supports_tokens' => true,
									'required'        => true,
								),
							),
							'add_row_button'    => __( 'Add pair', 'uncanny-automator-pro' ),
							'remove_row_button' => __( 'Remove pair', 'uncanny-automator-pro' ),
						),
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
	public function update_user( $user_id, $action_data, $recipe_id, $args ) {


		global $uncanny_automator;
		global $wpdb;
		$error_message = '';

		$userdata['ID'] = $user_id;
		$user_obj       = new \WP_User( (int) $user_id );
		$user_roles     = $user_obj->roles;
		if ( in_array( 'administrator', $user_roles ) ) {
			$error_message                       = __( 'For security, the update user action cannot be applied to Administrators.', 'uncanny-automator-pro' );
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		if ( isset( $action_data['meta']['USERNAME'] ) && ! empty( $action_data['meta']['USERNAME'] ) ) {
			$username = $uncanny_automator->parse->text( $action_data['meta']['USERNAME'], $recipe_id, $user_id, $args );
			if ( ! validate_username( $username ) ) {
				$error_message .= sprintf(
				/* translators: Update a {{user}} - Error while updating a user */
					__( 'Invalid username: %1$s', 'uncanny-automator-pro' ),
					$username );
			} elseif ( username_exists( $username ) !== false && username_exists( $username ) != ( $user_id ) ) {
				$error_message .= sprintf(
				/* translators: Update a {{user}} - Error while updating a user */
					__( 'Username already exists: %1$s', 'uncanny-automator-pro' ),
					$username );
			} else {
				$wpdb->update( $wpdb->users, array( 'user_login' => $username ), array( 'ID' => $user_id ) );
			}
		}

		if ( isset( $action_data['meta']['EMAIL'] ) && ! empty( $action_data['meta']['EMAIL'] ) ) {
			$email = $uncanny_automator->parse->text( $action_data['meta']['EMAIL'], $recipe_id, $user_id, $args );
			if ( ! is_email( $email ) ) {
				$error_message .= sprintf(
				/* translators: Update a {{user}} - Error while updating a user */
					__( 'Invalid email: %1$s', 'uncanny-automator-pro' ),
					$email );
			} elseif ( email_exists( $email ) !== false && email_exists( $email ) != $user_id ) {
				$error_message .= sprintf(
				/* translators: Update a {{user}} - Error while updating a user */
					__( 'Email address already exists: %1$s', 'uncanny-automator-pro' ),
					$email );
			} else {
				$userdata['user_email'] = $email;
			}
		}

		if ( isset( $action_data['meta']['PASSWORD'] ) && ! empty( $action_data['meta']['PASSWORD'] ) ) {
			$userdata['user_pass'] = $uncanny_automator->parse->text( $action_data['meta']['PASSWORD'], $recipe_id, $user_id, $args );
		}

		if ( isset( $action_data['meta']['WEBSITE'] ) && ! empty( $action_data['meta']['WEBSITE'] ) ) {
			$userdata['user_url'] = $uncanny_automator->parse->text( $action_data['meta']['WEBSITE'], $recipe_id, $user_id, $args );
		}

		if ( isset( $action_data['meta']['FIRSTNAME'] ) && ! empty( $action_data['meta']['FIRSTNAME'] ) ) {
			$userdata['first_name'] = $uncanny_automator->parse->text( $action_data['meta']['FIRSTNAME'], $recipe_id, $user_id, $args );
		}

		if ( isset( $action_data['meta']['LASTNAME'] ) && ! empty( $action_data['meta']['LASTNAME'] ) ) {
			$userdata['last_name'] = $uncanny_automator->parse->text( $action_data['meta']['LASTNAME'], $recipe_id, $user_id, $args );
		}

		if ( isset( $action_data['meta']['WPROLE'] ) && ! empty( $action_data['meta']['WPROLE'] ) && - 1 !== intval( $action_data['meta']['WPROLE'] ) ) {
			$userdata['role'] = $action_data['meta']['WPROLE'];
		}

		if ( isset( $userdata['user_pass'] ) && ! empty( $userdata['user_pass'] ) ) {
			add_filter( 'send_password_change_email', [ __CLASS__, 'send_password_change_email' ], 999, 3 );
		}
		$update_user = wp_update_user( $userdata );

		if ( is_wp_error( $update_user ) ) {
			$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id,
				/* translators: Update a {{user}} - Error while updating a user */
				__( 'Failed to update a user', 'uncanny-automator-pro' )
			);

			return;
		}

		$failed_meta_updates = [];


		if ( isset( $action_data['meta']['USERMETA_PAIRS'] ) && ! empty( $action_data['meta']['USERMETA_PAIRS'] ) ) {
			$fields = json_decode( $action_data['meta']['USERMETA_PAIRS'], true );

			foreach ( $fields as $meta ) {
				if ( isset( $meta['meta_key'] ) && ! empty( $meta['meta_key'] ) && isset( $meta['meta_value'] ) && ! empty( $meta['meta_value'] ) ) {
					$key   = $uncanny_automator->parse->text( $meta['meta_key'], $recipe_id, $user_id, $args );
					$value = $uncanny_automator->parse->text( $meta['meta_value'], $recipe_id, $user_id, $args );
					update_user_meta( $user_id, $key, $value );
				} else {
					$failed_meta_updates[ $meta['meta_key'] ] = $meta['meta_value'];
				}
			}
		}

		if ( ! empty( $failed_meta_updates ) ) {
			$failed_keys = "'" . implode( "','", array_keys( $failed_meta_updates ) ) . "'";
			$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id, sprintf(
			/* translators: Create a {{user}} - Error while creating a new user */
				__( 'Meta keys failed to update: %1$s', 'uncanny-automator' ),
				$failed_keys ) );
		}

		$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id, $error_message );
	}

	/**
	 * Filters whether to send the password change email.
	 *
	 * @param bool $send Whether to send the email.
	 * @param array $user The original user array.
	 * @param array $userdata The updated user array.
	 *
	 * @return bool
	 */
	public static function send_password_change_email( $send, $user, $userdata ) {
		// We do not want to upset user :)
		$send = false;

		return $send;
	}
}
