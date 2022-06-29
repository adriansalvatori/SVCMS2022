<?php


namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Automator_Recipe_Process_Complete;

/**
 * Class Automator_Pro_Recipe_Process_Complete
 *
 * @package Uncanny_Automator_Pro
 */
class Automator_Pro_Recipe_Process_Complete extends Automator_Recipe_Process_Complete {

	/**
	 * @var Automator_Pro_Recipe_Process_Anon
	 */
	public $anon;

	/**
	 * Automator_Pro_Recipe_Process_Complete constructor.
	 */
	public function __construct() {
		add_filter( 'automator_maybe_continue_recipe_process', array(
			$this,
			'uap_maybe_continue_recipe_process_func',
		) );
	}

	/**
	 * @param $attributes
	 *
	 * @return mixed
	 */
	public function uap_maybe_continue_recipe_process_func( $attributes ) {
		global $uncanny_automator;
		$maybe_continue_recipe_process = $attributes['maybe_continue_recipe_process'];
		$recipe_id                     = $attributes['recipe_id'];
		$trigger_id                    = $attributes['trigger_id'];
		$user_id                       = $attributes['user_id'];
		$recipe_log_id                 = $attributes['recipe_log_id'];
		$trigger_log_id                = $attributes['trigger_log_id'];
		$args                          = $attributes['args'];
		$trigger_data                  = $uncanny_automator->get_recipe_data( 'uo-trigger', $recipe_id );

		$recipe_type = (string) $uncanny_automator->utilities->get_recipe_type( $recipe_id );
		if ( 'anonymous' === (string) $recipe_type ) {
			do_action( 'uap_before_anon_user_action_completed', $user_id, $recipe_id, $trigger_log_id, $args, $attributes );
			global $wpdb;
			$user_action_result = $this->maybe_run_anonymous_recipe_user_actions( $recipe_id, $user_id, $recipe_log_id, $trigger_data, $args );
			if ( false === $user_action_result ) {
				return array(
					'maybe_continue_recipe_process' => $maybe_continue_recipe_process,
					'recipe_id'                     => $recipe_id,
					'user_id'                       => $user_id,
					'recipe_log_id'                 => $recipe_log_id,
					'args'                          => $args,
				);
			}
			$user_action_status = isset( $user_action_result['status'] ) ? $user_action_result['status'] : false;
			$user_action_data   = isset( $user_action_result['data'] ) ? $user_action_result['data'] : array();
			$user_id            = array_key_exists( 'user_id', $user_action_data ) ? $user_action_data['user_id'] : false;

			if ( false === $user_action_status ) {
				$maybe_continue_recipe_process = false;
				$recipe_action_data            = $uncanny_automator->get_recipe_data( 'uo-action', $recipe_id );
				foreach ( $recipe_action_data as $action_data ) {
					//Create New User - Existing user found matching
					$error_message = is_array( $user_action_data ) && key_exists( 'message', $user_action_data ) ? $user_action_data['message'] : __( 'There was an error completing recipe', 'uncanny-automator-pro' );

					do_action( 'uap_before_user_action_do_nothing_completed', $user_action_result, $user_id, $action_data, $recipe_id, $trigger_log_id, $args );

					$action_data['do-nothing'] = true;

					if ( key_exists( 'error', $user_action_data ) ) {
						$action_data['complete_with_errors'] = true;
					}

					$action_data['recipe_log_id'] = $recipe_log_id;
					$args['user_action_message']  = $user_action_data['message'];

					if ( $user_id ) {
						//attempt to update anonymous user to actual user!
						$table_name = $wpdb->prefix . 'uap_recipe_log';
						$wpdb->update(
							$table_name,
							array( 'user_id' => $user_id ),
							array( 'ID' => $args['recipe_log_id'] ),
							array( '%d' ),
							array( '%d' )
						);
					}

					//$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message, $recipe_log_id, $args ); //Complete action with errors!
					do_action( 'uap_after_user_action_do_nothing_completed', $user_action_result, $user_id, $action_data, $recipe_id, $trigger_log_id, $args );
					$args['do-nothing'] = true;
					//$uncanny_automator->complete_recipe( $recipe_id, $user_id, $recipe_log_id, $args );
				}
			} else {
				//attempt to update anonymous user to actual user!
				$args['user_action_message'] = $user_action_data['message'];
				do_action( 'uap_after_user_action_completed', $user_action_result, $user_id, $recipe_id, $trigger_log_id, $args );
			}
			//if $user_id found, update triggers, actions, recipe logs
			if ( $user_id && 0 !== $user_id ) {
				$args['user_id'] = $user_id;
				$table_name      = $wpdb->prefix . 'uap_trigger_log';
				$wpdb->update(
					$table_name,
					array( 'user_id' => $user_id ),
					array( 'ID' => $args['get_trigger_id'] ),
					array( '%d' ),
					array( '%d' )
				);
				//attempt to update anonymous user to actual user!
				$table_name = $wpdb->prefix . 'uap_recipe_log';
				$wpdb->update(
					$table_name,
					array( 'user_id' => $user_id ),
					array( 'ID' => $args['recipe_log_id'] ),
					array( '%d' ),
					array( '%d' )
				);
				//attempt to update anonymous user to actual user!
				$table_name = $wpdb->prefix . 'uap_trigger_log_meta';
				$wpdb->update(
					$table_name,
					array(
						'user_id'  => $user_id,
						'run_time' => current_time( 'mysql' ),
					),
					array( 'automator_trigger_log_id' => $args['get_trigger_id'] ),
					array( '%d', '%s' ),
					array( '%d' )
				);

				$run_number  = $wpdb->get_var( $wpdb->prepare( "SELECT run_number FROM $table_name WHERE automator_trigger_log_id = %d AND user_id = %d", $args['get_trigger_id'], $user_id ) );
				$parsed_data = key_exists( 'parsed_data', $user_action_result ) ? $user_action_result['parsed_data'] : array();

				$parsed_args = array(
					'user_id'        => $user_id,
					'trigger_id'     => $trigger_id,
					'trigger_log_id' => $trigger_log_id,
					'run_number'     => $run_number,
					'meta_key'       => 'parsed_data',
					'meta_value'     => maybe_serialize( $parsed_data ),
				);

				$uncanny_automator->insert_trigger_meta( $parsed_args );

			}

			$attributes = array(
				'maybe_continue_recipe_process' => $maybe_continue_recipe_process,
				'recipe_id'                     => $recipe_id,
				'user_id'                       => $user_id,
				'recipe_log_id'                 => $recipe_log_id,
				'args'                          => $args,
			);

			do_action( 'uap_after_anon_user_action_completed', $user_id, $recipe_id, $trigger_log_id, $attributes );
		}

		return $attributes;
	}

	/**
	 * Complete the trigger for the user
	 *
	 * @param array $args
	 *
	 * @return null
	 */
	public function anon_trigger( $args = array() ) {

		$user_id        = absint( $args['user_id'] );
		$trigger_id     = absint( $args['trigger_id'] );
		$recipe_id      = absint( $args['recipe_id'] );
		$trigger_log_id = absint( $args['get_trigger_id'] );
		$recipe_log_id  = absint( $args['recipe_log_id'] );

		// Set user ID
		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		if ( null === $trigger_id || ! is_numeric( $trigger_id ) ) {
			Utilities::log( 'ERROR: You are trying to complete a trigger without providing a trigger_id', 'complete_trigger ERROR', false, 'uap-errors' );

			return null;
		}

		if ( null === $recipe_id || ! is_numeric( $recipe_id ) ) {
			Utilities::log( 'ERROR: You are trying to complete a trigger without providing a recipe_id', 'complete_trigger ERROR', false, 'uap-errors' );

			return null;
		}
		// The trigger is about to be completed
		do_action( 'uap_before_anon_trigger_completed', $user_id, $trigger_id, $recipe_id, $trigger_log_id, $args );

		global $uncanny_automator;
		$trigger_code        = get_post_meta( $trigger_id, 'code', true );
		$trigger_integration = $uncanny_automator->get->trigger_integration_from_trigger_code( $trigger_code );
		if ( 0 === $uncanny_automator->plugin_status->get( $trigger_integration ) ) {

			// The plugin for this action is NOT active
			Utilities::log( 'ERROR: You are trying to complete ' . $trigger_code . ' and the plugin ' . $trigger_integration . ' is not active. ', 'complete_trigger ERROR', false, 'uap-errors' );

			return null;
		}

		global $wpdb;

		$update = array(
			'completed' => true,
			'date_time' => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
			//phpcs:ignore phpcs: WordPress.DateTime.RestrictedFunctions.date_date
		);

		$where = array(
			'user_id'              => $user_id,
			'automator_trigger_id' => $trigger_id,
			'automator_recipe_id'  => $recipe_id,
		);

		if ( null !== $trigger_log_id && is_int( $trigger_log_id ) ) {
			$where['ID'] = (int) $trigger_log_id;
		}

		if ( null !== $recipe_log_id && is_int( $recipe_log_id ) ) {
			$where['automator_recipe_log_id'] = (int) $recipe_log_id;
		}

		$update_format = array(
			'%d',
			'%s',
		);

		$where_format = array(
			'%d',
			'%d',
			'%d',
		);

		if ( ! empty( $trigger_log_id ) && is_int( $trigger_log_id ) ) {
			$where_format[] = '%d';
		}
		if ( ! empty( $recipe_log_id ) && is_int( $recipe_log_id ) ) {
			$where_format[] = '%d';
		}

		$table_name = $wpdb->prefix . 'uap_trigger_log';

		$wpdb->update(
			$table_name,
			$update,
			$where,
			$update_format,
			$where_format
		);

		/**
		 * Provide hook to developers to hook in to and
		 * do what they want to do with it
		 *
		 * @version 2.5
		 * @author  Saad
		 */
		$trigger_data = $uncanny_automator->get->trigger_sentence( $trigger_id, 'trigger_detail' );
		do_action( 'automator_complete_trigger_detail', $trigger_data, $args );

		$maybe_continue_recipe_process = true;
		//make sure that we have a User_ID selected for next step.
		$recipe_type = (string) $uncanny_automator->utilities->get_recipe_type( $recipe_id );
		if ( 'anonymous' === (string) $recipe_type ) {
			$trigger_data = $uncanny_automator->get_recipe_data( 'uo-trigger', $recipe_id );
			do_action( 'uap_before_anon_action_completed', $user_id, $recipe_id, $trigger_log_id, $args );
			$user_action_result = $this->maybe_run_anonymous_recipe_user_actions( $recipe_id, $user_id, $recipe_log_id, $trigger_data, $args );
			if ( false === $user_action_result ) {
				return array(
					'maybe_continue_recipe_process' => $maybe_continue_recipe_process,
					'recipe_id'                     => $recipe_id,
					'user_id'                       => $user_id,
					'recipe_log_id'                 => $recipe_log_id,
					'args'                          => $args,
				);
			}
			$user_action_status = $user_action_result['status'];
			$user_action_data   = $user_action_result['data'];
			$user_id            = key_exists( 'user_id', $user_action_data ) ? $user_action_data['user_id'] : false;

			if ( false !== $user_action_status ) {
				$maybe_continue_recipe_process = false;
				$recipe_action_data            = $uncanny_automator->get_recipe_data( 'uo-action', $recipe_id );
				foreach ( $recipe_action_data as $action_data ) {
					//Create New User - Existing user found matching
					$error_message = key_exists( 'message', $user_action_data ) ? $user_action_data['message'] : __( 'There was an error completing recipe', 'uncanny-automator-pro' );

					do_action( 'uap_before_user_action_do_nothing_completed', $user_action_result, $user_id, $action_data, $recipe_id, $trigger_log_id, $args );

					$action_data['do-nothing'] = true;

					if ( key_exists( 'error', $user_action_data ) ) {
						$action_data['complete_with_errors'] = true;
					}

					$action_data['recipe_log_id'] = $recipe_log_id;
					$args['user_action_message']  = $user_action_data['message'];

					if ( $user_id ) {
						//attempt to update anonymous user to actual user!
						$table_name = $wpdb->prefix . 'uap_recipe_log';
						$wpdb->update(
							$table_name,
							array( 'user_id' => $user_id ),
							array( 'ID' => $args['recipe_log_id'] ),
							array( '%d' ),
							array( '%d' )
						);
					}

					//$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message, $recipe_log_id, $args ); //Complete action with errors!
					do_action( 'uap_after_user_action_do_nothing_completed', $user_action_result, $user_id, $action_data, $recipe_id, $trigger_log_id, $args );
					$args['do-nothing'] = true;
					//$uncanny_automator->complete_recipe( $recipe_id, $user_id, $recipe_log_id, $args );
				}
			} else {
				//attempt to update anonymous user to actual user!
				$args['user_action_message'] = $user_action_data['message'];
				do_action( 'uap_after_user_action_completed', $user_action_result, $user_id, $recipe_id, $trigger_log_id, $args );
			}
			//if $user_id found, update triggers, actions, recipe logs
			if ( $user_id && 0 !== $user_id ) {
				$args['user_id'] = $user_id;
				$table_name      = $wpdb->prefix . 'uap_trigger_log';
				$wpdb->update(
					$table_name,
					array( 'user_id' => $user_id ),
					array( 'ID' => $args['get_trigger_id'] ),
					array( '%d' ),
					array( '%d' )
				);
				//attempt to update anonymous user to actual user!
				$table_name = $wpdb->prefix . 'uap_recipe_log';
				$wpdb->update(
					$table_name,
					array( 'user_id' => $user_id ),
					array( 'ID' => $args['recipe_log_id'] ),
					array( '%d' ),
					array( '%d' )
				);

				//attempt to update anonymous user to actual user!
				$table_name = $wpdb->prefix . 'uap_trigger_log_meta';
				$wpdb->update(
					$table_name,
					array(
						'user_id'  => $user_id,
						'run_time' => current_time( 'mysql' ),
					),
					array( 'automator_trigger_log_id' => $args['get_trigger_id'] ),
					array( '%d', '%s' ),
					array( '%d' )
				);

				$run_number  = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT run_number FROM $table_name WHERE automator_trigger_log_id = %d AND user_id = %d",
						$args['get_trigger_id'],
						$user_id
					)
				);
				$parsed_data = key_exists( 'parsed_data', $user_action_result ) ? $user_action_result['parsed_data'] : array();

				$parsed_args = array(
					'user_id'        => $user_id,
					'trigger_id'     => $trigger_id,
					'trigger_log_id' => $trigger_log_id,
					'run_number'     => $run_number,
					'meta_key'       => 'parsed_data',
					'meta_value'     => maybe_serialize( $parsed_data ),
				);

				$uncanny_automator->insert_trigger_meta( $parsed_args );

			}
		}

		// If all triggers for the recipe are completed
		if ( $maybe_continue_recipe_process && $this->triggers_completed( $recipe_id, $user_id, $recipe_log_id, $args ) ) {
			$this->complete_actions( $recipe_id, $user_id, $recipe_log_id, $args );
		}
	}


	/**
	 * @param $recipe_id
	 * @param $maybe_user_id
	 * @param $recipe_log_id
	 * @param $trigger_data
	 * @param $args
	 *
	 * @return array|bool
	 */
	public function maybe_run_anonymous_recipe_user_actions( $recipe_id, $maybe_user_id, $recipe_log_id, $trigger_data, $args ) {
		//we have a valid user_id, return user ID..
		//We expect a null or 0 user ID for anon function
		//$p           = array(); //for logs
		//$m           = array(); // for logs
		$replace_args = array(
			'recipe_id'      => $recipe_id,
			'recipe_log_id'  => $recipe_log_id,
			'trigger_id'     => $args['trigger_id'],
			'trigger_log_id' => $args['get_trigger_id'],
			'run_number'     => $args['run_number'],
			'user_id'        => $maybe_user_id,
		);
		$fields       = get_post_meta( $recipe_id, 'fields', true );
		$user_action  = get_post_meta( $recipe_id, 'source', true );
		$data         = array();
		$parsed_data  = array();
		$fallback     = array();

		if ( $fields ) {
			$fallback = array_key_exists( 'fallback', $fields ) ? $fields['fallback'] : array();
			foreach ( $fields as $key => $field ) {
				if ( preg_match_all( '/\{\{\s*(.*?)\s*\}\}/', $field, $arr ) ) {
					//Match {{}} tokens
					$matches = $arr[1];
					//$m[]     = $matches;
					foreach ( $matches as $match ) {
						$pieces                 = explode( ':', $match );
						$replace_args['pieces'] = $pieces;
						//$p[]    = $pieces;
						// Add default tokens to $data so that
						// we can replace multiple tokens
						if ( ! key_exists( $key, $data ) ) {
							$data[ $key ] = $field;
						}
						//Attempting to decode data!
						//$val                   = apply_filters( 'automator_maybe_parse_token', '', $pieces, $recipe_id, $args, $maybe_user_id, $replace_args = array() );
						$val          = apply_filters( 'automator_maybe_parse_token', '', $pieces, $recipe_id, $trigger_data, $maybe_user_id, $replace_args );
						$data[ $key ] = str_replace( '{{' . $match . '}}', $val, $data[ $key ] );

						$parsed_data[ $field ] = $val;
					}
				} else {
					$data[ $key ]        = $field;
					$parsed_data[ $key ] = $field;
				}
			}
		}

		$user_data = array();
		if ( ! empty( $data ) ) {

			$user_data['first_name']         = key_exists( 'firstName', $data ) ? $data['firstName'] : '';
			$user_data['last_name']          = key_exists( 'lastName', $data ) ? $data['lastName'] : '';
			$user_data['user_email']         = key_exists( 'email', $data ) ? $data['email'] : '';
			$user_data['user_login']         = key_exists( 'username', $data ) ? $data['username'] : $user_data['user_email'];
			$user_data['role']               = key_exists( 'role', $data ) ? $data['role'] : apply_filters( 'uap_default_user_role', get_option( 'default_role', 'subscriber' ) );
			$user_data['user_pass']          = key_exists( 'password', $data ) ? $data['password'] : apply_filters( 'uap_maybe_generate_anonymous_user_password', wp_generate_password(), $user_data );
			$user_data['prioritized_field']  = key_exists( 'prioritizedField', $fields ) ? $fields['prioritizedField'] : '';
			$user_data['unique_field_value'] = key_exists( 'uniqueFieldValue', $data ) ? $data['uniqueFieldValue'] : '';
			$user_data['unique_field']       = key_exists( 'uniqueField', $fields ) ? $fields['uniqueField'] : '';

			if ( 'newUser' === (string) $user_action ) {
				return $this->user_action_on_new_user( $user_data, $fallback, $data );
			} elseif ( 'existingUser' === (string) $user_action ) {
				return $this->user_action_on_existing_user( $user_data, $fallback, $data );
			}
		}

		//No user action ran.. return false
		//return [ 'status' => false, 'data' => array() ];
		return false;
	}

	/**
	 * @param $user_data
	 * @param $fallback
	 * @param array $parsed_data
	 *
	 * @return array
	 */
	public function user_action_on_new_user( $user_data, $fallback, $parsed_data = array() ) {
		$return  = array();
		$user_id = $this->verify_if_user_exists_for_new_user_action( $user_data );
		if ( ! $user_id ) {
			//@var $user_id not defined yet, insert new user
			$user_id = wp_insert_user( $user_data );

			if ( ! is_wp_error( $user_id ) ) {
				$data = array(
					'user_id'  => $user_id,
					'fallback' => $fallback,
					'action'   => 'newUser',
					'message'  => sprintf(
					/* translators: 1. The user email */
						__( 'New user created (%1$s)', 'uncanny-automator-pro' ),
						$user_data['user_email']
					),
				);

				if ( isset( $parsed_data['logUserIn'] ) && 'yes' === $parsed_data['logUserIn'] ) {
					$user = get_user_by( 'ID', $user_id );
					wp_set_current_user( $user_id, $user->user_login );
					wp_set_auth_cookie( $user_id );
					do_action( 'wp_login', $user->user_login, $user, false );
				}

				$return = array(
					'status'      => true,
					'data'        => $data,
					'parsed_data' => $parsed_data,
				);
			} else {
				$data = array(
					'user_id'  => email_exists( $user_data['user_email'] ),
					'fallback' => 'do-nothing',
					'action'   => 'newUser',
					'error'    => true,
					'message'  => sprintf(
					/* translators: 1. The user email, 2. The error */
						__( 'Create new user failed (%1$s). %2$s', 'uncanny-automator-pro' ),
						$user_data['user_email'],
						$user_id->get_error_message()
					),
				);

				$return = array(
					'status' => false,
					'data'   => $data,
				);
			}
		} else {
			switch ( $fallback ) {
				case 'do-nothing':
					$user = get_user_by( 'ID', $user_id );
					$data = array(
						'user_id'  => $user_id,
						'fallback' => $fallback,
						'action'   => 'newUser',
						'message'  => sprintf(
						/* translators: 1. The user email */
							__( 'Existing user found matching (%1$s), do nothing', 'uncanny-automator-pro' ),
							$user->user_login
						),
					);

					$return = array(
						'status' => false,
						'data'   => $data,
					);
					break;
				case 'select-existing-user':
					//Select Existing User
					$status = true;
					$user   = get_user_by( 'ID', $user_id );
					$data   = array(
						'user_id'  => $user_id,
						'fallback' => $fallback,
						'action'   => 'newUser',
						'message'  => sprintf(
						/* translators: 1. The user email */
							__( 'Existing user found matching (%1$s), selected existing user', 'uncanny-automator-pro' ),
							$user->user_email
						),
					);

					if ( in_array( 'administrator', $user->roles, true ) ) {
						/* translators: 1. The user email */
						$data['message']  = sprintf( __( 'Existing user found matching (%1$s), cannot select administrators', 'uncanny-automator-pro' ), $user->user_email );
						$data['fallback'] = 'do-nothing';
						$status           = false;
					}

					$return = array(
						'status'      => $status,
						'data'        => $data,
						'parsed_data' => $parsed_data,
					);
					break;
			}
		}

		return $return;
	}

	/**
	 * @param $user_data
	 * @param $fallback
	 * @param array $parsed_data
	 *
	 * @return array
	 */
	public function user_action_on_existing_user( $user_data, $fallback, $parsed_data = array() ) {
		$user_id = $this->verify_if_user_exists_for_existing_user_action( $user_data );
		$return  = array();
		if ( ! $user_id ) {
			//User not found
			switch ( $fallback ) {
				case 'do-nothing':
					$data = array(
						'user_id'  => 0,
						'fallback' => $fallback,
						'action'   => 'existingUser',
						'message'  => sprintf(
						/* translators: 1. The user ID, email or username */
							__( 'User not found matching (%1$s), do nothing', 'uncanny-automator-pro' ),
							$user_data['unique_field_value']
						),
					);

					$return = array(
						'status' => false,
						'data'   => $data,
					);
					break;
				case 'create-new-user':
					//Create a new user
					$user_id = wp_insert_user( $user_data );
					if ( ! is_wp_error( $user_id ) ) {
						$data = array(
							'user_id'  => $user_id,
							'fallback' => $fallback,
							'action'   => 'existingUser',
							'message'  => sprintf(
							/* translators: 1. The user email */
								__( 'User not found matching (%1$s), new user created', 'uncanny-automator-pro' ),
								$user_data['user_email']
							),
						);

						if ( isset( $parsed_data['logUserIn'] ) && 'yes' === $parsed_data['logUserIn'] ) {
							$user = get_user_by( 'ID', $user_id );
							wp_set_current_user( $user_id, $user->user_login );
							wp_set_auth_cookie( $user_id );
							do_action( 'wp_login', $user->user_login, $user, false );
						}

						$return = array(
							'status'      => true,
							'data'        => $data,
							'parsed_data' => $parsed_data,
						);
					} else {
						$data = array(
							'user_id'  => 0,
							'fallback' => $fallback,
							'action'   => 'existingUser',
							'error'    => true,
							'message'  => sprintf(
							/* translators: 1. The user email, 2. The error */
								__( 'Create new user failed (%1$s). %2$s', 'uncanny-automator-pro' ),
								$user_data['user_email'],
								$user_id->get_error_message()
							),
						);

						$return = array(
							'status' => false,
							'data'   => $data,
						);
					}
					break;
			}
		} else {
			//User found!
			$status = true;
			$user   = get_user_by( 'ID', $user_id );
			$data   = array(
				'user_id'  => $user_id,
				'fallback' => $fallback,
				'action'   => 'existingUser',
				'message'  => sprintf(
				/* translators: 1. The user email */
					__( 'User found matching (%1$s), selecting user', 'uncanny-automator-pro' ),
					$user->user_email
				),
			);

			if ( in_array( 'administrator', $user->roles, true ) ) {
				$data['message']  = sprintf(
				/* translators: 1. The user email */
					__( 'Existing user found matching (%1$s), cannot select administrators', 'uncanny-automator-pro' ),
					$user->user_email
				);
				$data['fallback'] = 'do-nothing';
				$status           = false;
			}

			$return = array(
				'status'      => $status,
				'data'        => $data,
				'parsed_data' => $parsed_data,
			);
		}

		return $return;
	}

	/**
	 * @param $user_data
	 * @param $user_action
	 *
	 * @return bool|false|int
	 */
	public function verify_if_user_exists_for_new_user_action( $user_data ) {
		//If there's a priority to match
		$priority = 'email';
		if ( key_exists( 'prioritized_field', $user_data ) ) {
			$priority = (string) $user_data['prioritized_field'];
		}

		switch ( $priority ) {
			case 'username':
				$user_id = username_exists( $user_data['user_login'] );
				if ( $user_id ) {
					return $user_id;
				} elseif ( ! empty( $user_data['user_email'] ) ) {
					$user_id = email_exists( $user_data['user_email'] );
					if ( $user_id ) {
						return $user_id;
					}
				}

				break;

			case 'email':
			default:
				$user_id = email_exists( $user_data['user_email'] );
				if ( $user_id ) {
					return $user_id;
				} elseif ( empty( $user_data['user_login'] ) ) {
					$user_id = username_exists( $user_data['user_login'] );
					if ( $user_id ) {
						return $user_id;
					}
				}

				break;
		}

		return false;

	}

	/**
	 * @param $user_data
	 * @param $user_action
	 *
	 * @return bool|false|int
	 */
	public function verify_if_user_exists_for_existing_user_action( $user_data ) {
		//If there's a priority to match
		$unique_field = ( key_exists( 'unique_field', $user_data ) && ! empty( $user_data['unique_field'] ) ) ? $user_data['unique_field'] : 'email';
		$value        = $user_data['unique_field_value'];

		if ( empty( $value ) ) {
			return false;
		}

		switch ( $unique_field ) {
			case 'username':
				$user_id = username_exists( $value );

				return ! is_wp_error( $user_id ) ? $user_id : false;
			case 'id':
				$user_id = get_user_by( 'ID', $value );
				if ( $user_id instanceof \WP_User ) {
					return $user_id->ID;
				}

				return ! is_wp_error( $user_id ) ? $user_id : false;
			case 'email':
			default:
				$user_id = email_exists( $value );

				return ! is_wp_error( $user_id ) ? $user_id : false;
		}
	}
}
