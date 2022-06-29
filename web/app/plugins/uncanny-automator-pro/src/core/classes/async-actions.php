<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Async_Actions
 * @package Uncanny_Automator
 */
class Async_Actions {
	
	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'automator_before_action_executed', array( $this, 'maybe_postpone_action' ) );

		add_filter( 'automator_get_action_completed_status', array( $this, 'change_action_completed_status' ), 10, 7 );

		add_filter( 'automator_before_action_created', array( $this, 'maybe_complete_async_action' ), 10, 7 );

		add_filter( 'automator_action_log_date_time', array( $this, 'adjust_action_log_date_time' ), 10, 2 );

		add_filter( 'automator_action_created', array( $this, 'store_async_job_id' ), 10, 1 );

		add_filter( 'automator_action_log_status', array( $this, 'action_log_status' ), 10, 2 );
		
		add_action( 'automator_async_run', array( $this, 'run' ) );

		add_action( 'wp_ajax_cancel_async_run', array( $this, 'cancel_async_run') );
		
	}
	
	/**
	 * maybe_postpone_action
	 * 
	 * This function will check if there is an async_mode meta set and will schedule the action is needed.
	 *
	 * @param  array $action
	 * @return array
	 */
	public function maybe_postpone_action( $action ) {

		if ( ! isset( $action['action_data']['meta']['async_mode'] ) ) {
			return $action;
		}

		$action['action_data']['async'] = $this->generate_async_settings( $action );

		if ( $action['action_data']['async']['timestamp'] < current_time( 'timestamp', 1 ) ) {
			unset( $action['action_data']['async'] );
			automator_log( 'maybe_postpone_action: time is in the past, running action as non-scheduled.' );
			return $action;
		}

		$action['action_data']['async']['job_id']  = $this->postpone( $action );

		$action['action_data']['args']['async'] = true;

		automator_log( 'Action was scheduled with a job ID: ' . $action['action_data']['async']['job_id'] );

		$this->log_action( $action );

		$action['process_further'] = false;

		return $action;
	}
	
	/**
	 * generate_async_settings
	 * 
	 * This function will generate all the settings required for scheduling an action, such as mode, status and timestamp.
	 *
	 * @param  array $action
	 * @return array
	 */
	public function generate_async_settings( $action ) {
		$format = get_option('date_format') . ' ' . get_option('time_format');

		$settings['timestamp'] = $this->get_timestamp( $action );
		
		$settings['mode']      = $action['action_data']['meta']['async_mode'];
		$settings['status']    = 'waiting';

		return $settings;
	}
	
	/**
	 * get_timestamp
	 * 
	 * This function will generate a timestamp, when an action is suposed to be scheduled.
	 *
	 * @param  array $action
	 * @return int
	 */
	public function get_timestamp( $action ) {

		$async_mode = $action['action_data']['meta']['async_mode'];
		$timestamp  = current_time( 'timestamp' );

		switch ( $async_mode ) {
			case 'delay':
				$timestamp = $this->get_delay_seconds( $action );
				break;
			case 'schedule':
				$timestamp = $this->get_schedule_seconds( $action );
				break;
			default:
				// Do nothing
				break;
		}

		return $timestamp;
	}
	
	/**
	 * postpone
	 * 
	 * This function will create a new job with the Action Scheduler and pass back its ID.
	 *
	 * @param  array $action
	 * @return int
	 */
	public function postpone( $action ) {
		$timestamp = $action['action_data']['async']['timestamp'];

		$hook = 'automator_async_run';
		$args = array( $action );
		$group = 'Uncanny Automator';

		return as_schedule_single_action( $timestamp, $hook, $args, $group );
	}
	
	/**
	 * log_action
	 * 
	 * This function will go through the action process to create a record in Automator's action log
	 * The process will be intercepted later to change the completed status
	 *
	 * @param  array $action
	 * @return void
	 */
	public function log_action( $action ) {
		extract( $action );
		$error_message = '';
		$recipe_log_id = $action_data['recipe_log_id'];
		Automator()->complete->action( $user_id, $action_data, $recipe_id, $error_message, $recipe_log_id, $args );
	}
	
	/**
	 * get_delay_seconds
	 *
	 * This fnction will translate the values from the delay UI into a timestamp.
	 * 
	 * @param  array $action
	 * @return int
	 */
	public function get_delay_seconds( $action ) {

		$unit       = $action['action_data']['meta']['async_delay_unit'];
		$number     = (int) $action['action_data']['meta']['async_delay_number'];
		$multiplier = 1;

		switch ( $unit ) {
			case 'minutes':
				$multiplier = 60;
				break;
			case 'hours':
				$multiplier = 60 * 60;
				break;
			case 'days':
				$multiplier = 60 * 60 * 24;
				break;
			case 'years':
				$multiplier = 60 * 60 * 24 * 365;
				break;
			default:
				// Do nothing
				break;
		}

		return current_time( 'timestamp', 1 ) + $number * $multiplier;
	}
	
	/**
	 * get_schedule_seconds
	 * 
	 * This fnction will translate the values from the schedule UI into a timestamp.
	 *
	 * @param  array $action
	 * @param  mixed $gmt
	 * @return void
	 */
	public function get_schedule_seconds( $action ) {
		
		$date = $action['action_data']['meta']['async_schedule_date'];
		$date_format = get_option('date_format');

		$time = $action['action_data']['meta']['async_schedule_time'];
		//$time_format = get_option('time_format');
		$time_format = "g:i A";
		$date_time = \DateTime::createFromFormat( $date_format . " " . $time_format, $date . " " . $time, wp_timezone() );

		if ( ! $date_time ) {
			automator_log( 'DateTime::createFromFormat failed: ' . var_export( \DateTime::getLastErrors(), true ) );
			automator_log( '$date: ' . var_export( $date , true ) );
			automator_log( '$date_format: ' . var_export( $date_format , true ) );
			automator_log( '$time: ' . var_export( $time , true ) );
			automator_log( '$time_format: ' . var_export( $time_format , true ) );
			automator_log( 'wp_timezone(): ' . var_export( wp_timezone() , true ) );
			return current_time( 'timestamp', 1 );
		}

		$timestamp = $date_time->getTimestamp();

		return $timestamp;
	}
	
	/**
	 * run
	 * 
	 * This action will run the scheduled actions, when the time has come.
	 *
	 * @param  array $action
	 * @return void
	 */
	public function run( $action ) {

		$action = apply_filters( 'automator_pro_before_async_action_executed', $action );

		if ( isset( $action['process_further'] ) && false === $action['process_further'] ) {

			automator_log( 'Action was skipped by automator_pro_before_async_action_executed filter.' );

			return;
		}

		$action_code = $action['action_data']['meta']['code'];

		$action_execution_function = Automator()->get->action_execution_function_from_action_code( $action_code );

		$action['action_data']['async']['status']       = 'completed';
		$action['action_data']['async']['completed_at'] = current_time( 'timestamp' );

		if ( isset( $action['process_further'] ) ) {
			unset( $action['process_further'] );
		}

		call_user_func_array( $action_execution_function, $action );
	}
	
	/**
	 * change_action_completed_status
	 * 
	 * This function will intercept the action completion process at automator_get_action_completed_status filter and swap the completed status with 5 if the action was scheduled earlier.
	 *
	 * @param  int $completed
	 * @param  int $user_id
	 * @param  array $action_data
	 * @param  int $recipe_id
	 * @param  string $error_message
	 * @param  int $recipe_log_id
	 * @param  array $args
	 * @return int
	 */
	public function change_action_completed_status( $completed, $user_id, $action_data, $recipe_id, $error_message, $recipe_log_id, $args ) {

		// If there was an error
		if ( $completed == 2 ) {
			return $completed;
		}

		// If async mode is not set
		if ( ! isset( $action_data['async']['mode'] ) ) {
			return $completed;
		}

		// If async status is not waiting
		if ( $action_data['async']['status'] !== 'waiting' ) {
			return $completed;
		}

		// Change the complted status to 5 (scheduled)
		$completed = 5;

		return $completed;
	}
	
	/**
	 * maybe_complete_async_action
	 * 
	 * This function will intercept the action completion process at automator_before_action_created filter and mark the action complete if it was run by the scheduler earlier.
	 *
	 * @param  bool $process_further
	 * @param  int $user_id
	 * @param  array $action_data
	 * @param  int $recipe_id
	 * @param  string $error_message
	 * @param  int $recipe_log_id
	 * @param  array $args
	 * @return bool
	 */
	public function maybe_complete_async_action( $process_further, $user_id, $action_data, $recipe_id, $error_message, $recipe_log_id, $args ) {

		if ( isset( $action_data['async']['status'] ) && $action_data['async']['status'] === 'completed' ) {

			Automator()->db->action->mark_complete( (int) $action_data['ID'], $recipe_log_id );

			do_action( 'uap_action_completed', $user_id, (int) $action_data['ID'], $recipe_id, $error_message, $args );

			Automator()->complete->recipe( $recipe_id, $user_id, $recipe_log_id, $args );

			$process_further = false;
		}

		return $process_further;
	}
	
	/**
	 * adjust_action_log_date_time
	 * 
	 * This function will intercept the action creation process at automator_action_log_date_time filter and adjust the date to make sure that it reflects the scheduled date.
	 *
	 * @param  mixed $date_time
	 * @param  mixed $action
	 * @return void
	 */
	public function adjust_action_log_date_time( $date_time, $action ) {

		if ( ! isset( $action['async']['timestamp'] ) ) {
			return $date_time;
		}

		date_default_timezone_set('UTC');

		$gmt_offset = get_option('gmt_offset');

		$timestamp_with_offset = (int) $action['async']['timestamp'] + $gmt_offset * 60 * 60;

		$date_time = date( 'Y-m-d H:i:s', $timestamp_with_offset );

		return $date_time;
	}
	
	/**
	 * store_async_job_id
	 * 
	 * This function will hook to automator_action_created action and store the scheduler's job ID in the DB.
	 *
	 * @param  array $args
	 * @return void
	 */
	public function store_async_job_id( $args ) {

		extract( $args );

		$job_id = ! empty( $action_data['async']['job_id'] ) ? $action_data['async']['job_id'] : '';

		if ( ! empty( $job_id  ) ) {
			$meta_key = 'async_job_id';
			Automator()->db->action->add_meta( (int) $user_id, (int) $action_log_id, (int) $action_id, $meta_key, (int) $job_id );
		}
	}
	
	/**
	 * action_log_status
	 * 
	 * This function will intercept the status of each action in the log table and replace it with the appropriate status if an action was scheduled or cancelled.
	 *
	 * @param  string $status
	 * @param  array $action
	 * @return string
	 */
	public function action_log_status( $status, $action ) {
		if ( 5 === (int) $action->action_completed ) {

			$status = esc_attr_x( 'Scheduled', 'Action', 'uncanny-automator' );
			$status .= ' ' . $this->cancel_link( $action );

			return $status;
		} 
		
		if ( 7 === (int) $action->action_completed ) {
			$status = esc_attr_x( 'Cancelled', 'Action', 'uncanny-automator' );
			return $status;
		}


		return $status;
	}
	
	/**
	 * cancel_link
	 * 
	 * Generates the cancellation link.
	 *
	 * @param  array $action
	 * @return string
	 */
	public function cancel_link( $action ) {
		return sprintf( '(<a href="#" onclick="cancelAsyncRun( event, %s, %s, %s )" class="uap-log-table__async-cancel">%s</a>)',
			$action->action_log_id,
			$action->automator_action_id,
			$action->recipe_log_id,
			esc_attr_x( 'cancel', 'Action', 'uncanny-automator' )
		);
	}
	
	/**
	 * cancel_async_run
	 * 
	 * This function handles the cancellation of a scheduled job when a corresponding link was clicked in the log table.
	 *
	 * @return void
	 */
	public function cancel_async_run() {

		if ( ! ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'load-recipes-ref' ) ) ) {
			wp_die();
		}

		$action_id = (int) $_POST['action_id'];
		$action_log_id = (int) $_POST['action_log_id'];
		$recipe_log_id = (int) $_POST['recipe_log_id'];

		$async_job_id = (int) Automator()->db->action->get_meta( $action_log_id, 'async_job_id' );

		$response = array();

		try {

			\ActionScheduler::store()->cancel_action(  $async_job_id );
			$response['success'] = true;
			Automator()->db->action->mark_complete( $action_id, $recipe_log_id, 7 );

			Automator()->complete->recipe( null, null, $recipe_log_id );

		} catch (\InvalidArgumentException $th) {

			automator_log( 'cancel_async_run for action ' . $action_log_id . ' failed with the following error: ' . $th->getMessage() );
			$response['success'] = false;
			$response['error'] = $th;
		} 
		
		echo json_encode( $response );
		
		wp_die(); // this is required to terminate immediately and return a proper response
	}

	
}
