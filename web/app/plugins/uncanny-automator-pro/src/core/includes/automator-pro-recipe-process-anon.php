<?php

namespace Uncanny_Automator_Pro;

use Uncanny_Automator\Automator_Recipe_Process_User;

/**
 * Class Automator_Pro_Recipe_Process_Anon
 * @package Uncanny_Automator_Pro
 */
class Automator_Pro_Recipe_Process_Anon extends Automator_Recipe_Process_User {
	/**
	 * Automator_Pro_Recipe_Process_Anon constructor.
	 */
	public function __construct() {
	}

	/**
	 * @param $args
	 * @param bool $mark_trigger_complete
	 *
	 * @return array|bool|int|null
	 */
	public function maybe_add_anon_trigger_entry( $args, $mark_trigger_complete = true ) {
		$is_signed_in = key_exists( 'is_signed_in', $args ) ? true : false;
		$is_anonymous = false;
		if ( ! is_user_logged_in() && false === $is_signed_in ) {
			$args['user_id']           = 0;
			$args['mark_as_anonymous'] = true;
			$is_anonymous              = true;
		}

		/** @var $is_anonymous , is false bail out and use non-anon trigger */
		if ( ! $is_anonymous ) {
			return parent::maybe_add_trigger_entry( $args, $mark_trigger_complete );
		}

		$check_trigger_code = key_exists( 'code', $args ) ? $args['code'] : null;
		$trigger_meta       = key_exists( 'meta', $args ) ? $args['meta'] : null;
		$post_id            = key_exists( 'post_id', $args ) ? $args['post_id'] : 0;
		$user_id            = key_exists( 'user_id', $args ) ? $args['user_id'] : wp_get_current_user()->ID;
		$matched_recipe_id  = key_exists( 'recipe_to_match', $args ) ? (int) $args['recipe_to_match'] : null;
		$matched_trigger_id = key_exists( 'trigger_to_match', $args ) ? (int) $args['trigger_to_match'] : null;
		$ignore_post_id     = key_exists( 'ignore_post_id', $args ) ? true : false;
		$is_webhook         = key_exists( 'is_webhook', $args ) ? true : false;
		$webhook_recipe     = key_exists( 'webhook_recipe', $args ) ? (int) $args['webhook_recipe'] : null;
		$get_trigger_id     = null;
		$result             = [];

		if ( is_null( $check_trigger_code ) ) {
			return null;
		}

		global $uncanny_automator;

		$args = [
			'code'             => $check_trigger_code,
			'meta'             => $trigger_meta,
			'post_id'          => $post_id,
			'user_id'          => $user_id,
			'recipe_to_match'  => $matched_recipe_id,
			'trigger_to_match' => $matched_trigger_id,
			'ignore_post_id'   => $ignore_post_id,
			'is_signed_in'     => $is_signed_in,
		];

		if ( $is_webhook ) {
			$recipes = $uncanny_automator->get->recipes_from_trigger_code( $check_trigger_code, $webhook_recipe );
		} else {
			$recipes = $uncanny_automator->get->recipes_from_trigger_code( $check_trigger_code );
		}
		foreach ( $recipes as $recipe ) {
			//loop only published
			if ( 'publish' !== $recipe['post_status'] ) {
				continue;
			}
			if ( 'user' === (string) $recipe['recipe_type'] && $is_anonymous ) {
				//If it's user recipe & user is not logged in.. skip recipe
				continue;
			}

			$recipe_id           = $recipe['ID'];
			$maybe_recipe_log    = $this->maybe_create_recipe_anon_log_entry( $recipe_id, $user_id, true, $args, true );
			$maybe_recipe_log_id = (int) $maybe_recipe_log['recipe_log_id'];

			foreach ( $recipe['triggers'] as $trigger ) {
				if ( ! empty( $matched_trigger_id ) && is_numeric( $matched_trigger_id ) && (int) $matched_trigger_id !== (int) $trigger['ID'] ) {
					continue;
				}

				$trigger_id          = $trigger['ID'];
				$trigger_post_status = $trigger['post_status'];

				if ( 'publish' !== $trigger_post_status ) {
					continue;
				}

				$get_trigger_id = $this->get_trigger_id( $args, $trigger, $recipe_id, $maybe_recipe_log_id, $ignore_post_id );

				if ( is_array( $get_trigger_id ) && false === $get_trigger_id['result'] ) {
					$result[] = $get_trigger_id;
					continue;
				}

				if ( ! $maybe_recipe_log['existing'] ) {
					//trigger validated.. add recipe log ID now!
					//$recipe_log_id = $this->maybe_create_recipe_anon_log_entry( $recipe_id, $user_id, true, $args, true, $maybe_recipe_log_id );
					$recipe_log_details = $this->maybe_create_recipe_anon_log_entry( $recipe_id, $user_id, true, $args );
					$recipe_log_id      = (int) $recipe_log_details['recipe_log_id'];
					//running again--after $recipe_log_id
					$get_trigger_id = $this->get_trigger_id( $args, $trigger, $recipe_id, $maybe_recipe_log_id, $ignore_post_id );
				} else {
					$recipe_log_id = $maybe_recipe_log_id;
				}

				$get_trigger_id = isset( $get_trigger_id['trigger_log_id'] ) ? $get_trigger_id['trigger_log_id'] : $get_trigger_id['get_trigger_id'];

				$numtimes_arg = [
					'recipe_id'      => $recipe_id,
					'trigger_id'     => $trigger_id,
					'trigger'        => $trigger,
					'user_id'        => $user_id,
					'recipe_log_id'  => $recipe_log_id,
					'trigger_log_id' => $get_trigger_id,
					'is_signed_in'   => $is_signed_in,
				];

				$trigger_steps_completed = $this->maybe_anon_trigger_num_times_completed( $numtimes_arg );

				//If -1 / Any option is used, save it's entry for tokens
				if ( ( isset( $trigger['meta'][ $trigger_meta ] ) && intval( '-1' ) === intval( $trigger['meta'][ $trigger_meta ] ) ) && true === $trigger_steps_completed['result'] ) {
					$meta_arg = [
						'recipe_id'      => $recipe_id,
						'trigger_id'     => $trigger_id,
						'user_id'        => $user_id,
						'recipe_log_id'  => $recipe_log_id,
						'trigger_log_id' => $get_trigger_id,
						'post_id'        => $post_id,
						'trigger'        => $trigger,
						'is_signed_in'   => $is_signed_in,
						'meta'           => $trigger_meta,
						'run_number'     => $uncanny_automator->get->next_run_number( $recipe_id, $user_id, true ),
					];
					#Utilities::log( $meta_arg, '$meta_arg', true, 'token-fixes' );

					$meta_results = $this->maybe_trigger_add_any_option_meta( $meta_arg, $trigger_meta );
					if ( false === $meta_results['result'] ) {
						Utilities::log( 'ERROR: You are trying to add entry ' . $trigger['meta'][ $trigger_meta ] . ' and post_id = ' . $post_id . '.', 'uap_maybe_add_meta_entry ERROR', false, 'uap-errors' );
					}

				}

				do_action( 'uap_after_trigger_run', $check_trigger_code, $post_id, $user_id, $trigger_meta );

				if ( true === $trigger_steps_completed['result'] ) {
					$args['get_trigger_id'] = $get_trigger_id;
					$args['trigger_log_id'] = $get_trigger_id;
					$args['recipe_id']      = $recipe_id;
					$args['trigger_id']     = $trigger_id;
					$args['recipe_log_id']  = $recipe_log_id;
					$args['post_id']        = $post_id;
					$args['is_signed_in']   = $is_signed_in;
					$args['run_number']     = $uncanny_automator->get->next_run_number( $recipe_id, $user_id, true );

					if ( 1 === + $mark_trigger_complete ) {
						$this->maybe_trigger_complete( $args );
					} else {
						$result[] = [ 'result' => true, 'args' => $args ];
					}
				}
			}
		}

		return $result;
	}

	/**
	 * @param $times_args
	 *
	 * @return array
	 */
	public function maybe_anon_trigger_num_times_completed( $times_args ) {
		global $uncanny_automator;
		$recipe_id      = key_exists( 'recipe_id', $times_args ) ? $times_args['recipe_id'] : null;
		$trigger_id     = key_exists( 'trigger_id', $times_args ) ? $times_args['trigger_id'] : null;
		$trigger        = key_exists( 'trigger', $times_args ) ? $times_args['trigger'] : null;
		$user_id        = key_exists( 'user_id', $times_args ) ? $times_args['user_id'] : null;
		$recipe_log_id  = key_exists( 'recipe_log_id', $times_args ) ? $times_args['recipe_log_id'] : null;
		$trigger_log_id = key_exists( 'trigger_log_id', $times_args ) ? $times_args['trigger_log_id'] : null;

		if ( null === $trigger_id || null === $trigger || null === $user_id ) {
			return [
				'result' => false,
				'error'  => 'One of the required field is missing.',
			];
		}

		// The number of times the current user needs to visit the post/page
		$num_times  = key_exists( 'NUMTIMES', $trigger['meta'] ) ? absint( $trigger['meta']['NUMTIMES'] ) : 1;
		$run_number = $uncanny_automator->get->trigger_run_number( $trigger_id, $trigger_log_id, $user_id );

		// How many times has this user triggered this trigger
		$user_num_times = null;

		$args = [
			'user_id'        => $user_id,
			'trigger_id'     => $trigger_id,
			'meta_key'       => 'NUMTIMES',
			'run_number'     => $run_number,
			'trigger_log_id' => $trigger_log_id,
		];

		if ( empty( $user_num_times ) ) {
			//This is first time user visited
			$args['meta_value'] = 1;
			$user_num_times     = 1;
		} else {

			$user_num_times ++;
			$args['run_number'] = $run_number + 1;
			$args['meta_value'] = 1;
		}

		$this->insert_trigger_meta( $args );
		//change completed from -1 to 0
		$this->maybe_change_recipe_log_to_zero( $recipe_id, $user_id, $recipe_log_id, true );

		// Move on if the user didn't trigger the trigger enough times
		if ( $user_num_times < $num_times ) {
			return [
				'result' => false,
				'error'  => 'Number of Times condition is not completed.',
			];
		}

		// If the trigger was hit the enough times then complete the trigger
		if ( $user_num_times >= $num_times ) {
			return [
				'result'     => true,
				'error'      => 'Number of times condition met.',
				'run_number' => $args['run_number'],
			];
		}

		return [
			'result' => false,
			'error'  => 'Default Return. Something is wrong.',
		];
	}

	/**
	 * @param $recipe_id
	 * @param $user_id
	 * @param null $maybe_add_log_id
	 * @param bool $maybe_anonymous
	 *
	 * @return int|null
	 */
	public function insert_recipe_anon_log( $recipe_id, $user_id, $maybe_add_log_id = null ) {
		global $uncanny_automator;
		global $wpdb;
		$table_name = $wpdb->prefix . 'uap_recipe_log';

		$num_times_recipe_run = false;

		if ( ! $num_times_recipe_run ) {
			$run_number = $uncanny_automator->get->next_run_number( $recipe_id, $user_id );

			$insert = array(
				'date_time'           => '0000-00-00 00:00:00',
				'user_id'             => $user_id,
				'automator_recipe_id' => $recipe_id,
				'completed'           => - 1,
				'run_number'          => $run_number,
			);

			$format = array(
				'%s',
				'%d',
				'%d',
				'%d',
			);

			if ( ! is_null( $maybe_add_log_id ) ) {
				$insert['ID'] = $maybe_add_log_id;
				$format[]     = '%d';
			}

			$r = $wpdb->insert( $table_name, $insert, $format );


			return (int) $wpdb->insert_id;
		}

		return null;
	}

	/**
	 * @param int $recipe_id
	 * @param int $user_id
	 * @param bool $create_recipe
	 * @param array $args
	 * @param bool $maybe_simulate
	 * @param null $maybe_add_log_id
	 *
	 * @return array
	 * @since 2.0
	 * @author Saad S. on Nov 15th, 2019
	 *
	 * Added $maybe_simulate in order to avoid unnecessary recipe logs in database.
	 * It'll return existing $recipe_log_id if there's one for a user & recipe, or
	 * simulate an ID for the next run.. The reason for simulate is to avoid unnecessary
	 * recipe_logs in the database since we insert recipe log first & check if trigger
	 * is valid after which means, recipe log is added and not used in this run.
	 * Once trigger is validated.. I pass $maybe_simulate ID to $maybe_add_log_id
	 * and insert recipe log at this point.
	 *
	 */
	public function maybe_create_recipe_anon_log_entry( $recipe_id, $user_id, $create_recipe = true, $args = [], $maybe_simulate = false, $maybe_add_log_id = null ) {
		global $wpdb;
		$anon = false;
		if ( key_exists( 'mark_as_anonymous', $args ) ) {
			$anon = true;
		}

		$recipe_log_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}uap_recipe_log WHERE completed != %d  AND automator_recipe_id = %d AND user_id = %d", 1, $recipe_id, $user_id ) );
		if ( ! $anon && $recipe_log_id && 0 !== absint( $user_id ) ) {
			return [ 'existing' => true, 'recipe_log_id' => $recipe_log_id, ];
		} elseif ( true === $maybe_simulate ) {
			/*
			 * @since 2.0
			 * @author Saad S.
			 */
			if ( ! is_null( $maybe_add_log_id ) ) {
				return [
					'existing'      => false,
					'recipe_log_id' => $this->insert_recipe_anon_log( $recipe_id, $user_id, $maybe_add_log_id ),
				];
				//return $this->insert_recipe_anon_log( $recipe_id, $user_id, $maybe_add_log_id );
			} else {
				$recipe_log_id = $wpdb->get_var( "SELECT `AUTO_INCREMENT` FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "' AND TABLE_NAME   = '{$wpdb->prefix}uap_recipe_log' LIMIT 0,1;" );
				//Utilities::log( $recipe_log_id, '$recipe_log_id', true, 'recipe_log_id' );
				if ( $recipe_log_id ) {
					return [ 'existing' => false, 'recipe_log_id' => $recipe_log_id, ];
					//return $recipe_log_id;
				}
			}
		} elseif ( true === $create_recipe ) {
			return [
				'existing'      => false,
				'recipe_log_id' => $this->insert_recipe_anon_log( $recipe_id, $user_id, null, $anon ),
			];
			//return $this->insert_recipe_anon_log( $recipe_id, $user_id, null, $anon );
		}

		return [ 'existing' => false, 'recipe_log_id' => null ];
		//return null;
	}

	/**
	 * Check if the recipe was completed
	 *
	 * @param $recipe_id null||int
	 * @param $user_id   null||int
	 * @param bool $is_anonymous v2.0
	 *
	 * @return null|bool
	 */
	public function is_anon_recipe_completed( $recipe_id = null, $user_id = null, $is_anonymous = false ) {

		if ( null === $recipe_id || ! is_numeric( $recipe_id ) ) {
			Utilities::log( 'ERROR: You are trying to check if a recipe is completed without providing a recipe_id.', 'is_recipe_completed ERROR', false, 'uap-errors' );

			return null;
		}

		if ( $is_anonymous && 0 === $user_id ) {
			return false;
		}

		// Set user ID
		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'uap_recipe_log';
		$results    = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(completed) FROM $table_name WHERE user_id = %d AND automator_recipe_id = %d AND completed = 1", $user_id, $recipe_id ) );

		if ( 0 === $results ) {
			return false;
		} else {
			$results = empty( $results ) ? 0 : $results;
			global $uncanny_automator;

			return $uncanny_automator->utilities->recipe_number_times_completed( $recipe_id, $results );
		}
	}
}
