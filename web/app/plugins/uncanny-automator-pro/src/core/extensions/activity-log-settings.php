<?php

namespace Uncanny_Automator_Pro;

/**
 * Class Activity_Log_Settings
 *
 * @package Uncanny_Automator_Pro
 */
class Activity_Log_Settings {

	/**
	 * @var string
	 */
	public static $cron_schedule = 'uapro_auto_purge_logs';

	/**
	 * Class constructor
	 */
	public function __construct() {
		// Add field to the settings page
		add_action( 'automator_settings_general_logs_content', array( $this, 'tab_output_auto_purge_fields' ) );

		add_action( self::$cron_schedule, array( $this, 'delete_old_logs' ) );
		add_action( 'admin_init', array( $this, 'save_prone_logs' ) );
		add_action( 'admin_init', array( $this, 'maybe_schedule_purge_logs' ) );
	}

	/**
	 *
	 */
	public function save_prone_logs() {
		if ( ! automator_filter_has_var( 'post_type' ) ) {
			return;
		}

		if ( ! automator_filter_has_var( 'page' ) ) {
			return;
		}

		if ( 'uo-recipe' !== automator_filter_input( 'post_type' ) ) {
			return;
		}

		if ( 'uncanny-automator-config' !== automator_filter_input( 'page' ) ) {
			return;
		}

		if ( 'logs' !== automator_filter_input( 'general' ) ) {
			return;
		}

		if (
			! automator_filter_has_var( 'uap_automator_purge_days_switch', INPUT_POST ) &&
			! automator_filter_has_var( 'uap_automator_purge_days', INPUT_POST )
		) {
			return;
		}

		// Get data
		$enable_auto_prune = automator_filter_input( 'uap_automator_purge_days_switch', INPUT_POST );
		$auto_prune_days   = automator_filter_input( 'uap_automator_purge_days', INPUT_POST );

		if ( ! empty( $auto_prune_days ) ) {
			// Save the number of days
			update_option( 'uap_automator_purge_days', $auto_prune_days );
		}

		// Check if we have to unschedule
		if ( empty( $enable_auto_prune ) || $enable_auto_prune == '0' ) {

			// Unschedule actions
			as_unschedule_all_actions( self::$cron_schedule );

			wp_safe_redirect(
				add_query_arg(
					array(
						'unscheduled' => 1,
					),
					$this->get_logs_settings_url()
				)
			);

			exit;

		}
			
	}

	/**
	 *
	 * Add values to settings tab
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function tab_output_auto_purge_fields() {
		// Check if the setting is enabled
		$is_enabled = ! empty( as_next_scheduled_action( self::$cron_schedule ) );

		// Interval
		$interval_number_of_days = get_option( 'uap_automator_purge_days' );

		// Load the view
		include Utilities::get_view( 'admin-settings/tab/general/logs/auto-prune-logs.php' );
	}

	/**
	 * Delete old logs.
	 */
	public function delete_old_logs() {

		$purge_days_limit = get_option( 'uap_automator_purge_days' );
		if ( empty( $purge_days_limit ) ) {
			return;
		}
		if ( intval( $purge_days_limit ) < 1 ) {
			return;
		}

		global $wpdb;

		$previous_time = gmdate( 'Y-m-d', strtotime( '-' . $purge_days_limit . ' days' ) );
		$recipes       = $wpdb->get_results( $wpdb->prepare( "SELECT `ID`, `automator_recipe_id` FROM {$wpdb->prefix}uap_recipe_log WHERE `date_time` < %s AND ( `completed` = %d OR `completed` = %d  OR `completed` = %d )", $previous_time, 1, 2, 9 ) );

		if ( empty( $recipes ) ) {
			return;
		}

		foreach ( $recipes as $recipe ) {
			$recipe_id               = absint( $recipe->automator_recipe_id );
			$automator_recipe_log_id = absint( $recipe->ID );

			// Purge recipe logs.
			if ( function_exists( 'automator_purge_recipe_logs' ) ) {
				automator_purge_recipe_logs( $recipe_id, $automator_recipe_log_id );
			} else {
				self::automator_purge_recipe_logs( $recipe_id, $automator_recipe_log_id );
			}

			// Purge trigger logs.
			if ( function_exists( 'automator_purge_trigger_logs' ) ) {
				automator_purge_trigger_logs( $recipe_id, $automator_recipe_log_id );
			} else {
				self::automator_purge_trigger_logs( $recipe_id, $automator_recipe_log_id );
			}

			// Purge action logs.
			if ( function_exists( 'automator_purge_action_logs' ) ) {
				automator_purge_action_logs( $recipe_id, $automator_recipe_log_id );
			} else {
				self::automator_purge_action_logs( $recipe_id, $automator_recipe_log_id );
			}

			// Purge closure logs.
			if ( function_exists( 'automator_purge_closure_logs' ) ) {
				automator_purge_closure_logs( $recipe_id, $automator_recipe_log_id );
			} else {
				self::automator_purge_closure_logs( $recipe_id, $automator_recipe_log_id );
			}
		}
	}

	/**
	 * @param $recipe_id
	 * @param $automator_recipe_log_id
	 */
	public static function automator_purge_recipe_logs( $recipe_id, $automator_recipe_log_id ) {
		global $wpdb;

		// delete from uap_recipe_log
		$wpdb->delete(
			$wpdb->prefix . 'uap_recipe_log',
			array(
				'automator_recipe_id' => $recipe_id,
				'ID'                  => $automator_recipe_log_id,
			)
		);
	}

	/**
	 * @param $recipe_id
	 * @param $automator_recipe_log_id
	 */
	public static function automator_purge_trigger_logs( $recipe_id, $automator_recipe_log_id ) {
		global $wpdb;
		$trigger_tbl      = $wpdb->prefix . 'uap_trigger_log';
		$trigger_meta_tbl = $wpdb->prefix . 'uap_trigger_log_meta';
		self::delete_logs( $trigger_tbl, $trigger_meta_tbl, 'automator_trigger_log_id', $recipe_id, $automator_recipe_log_id );
	}

	/**
	 * @param $recipe_id
	 * @param $automator_recipe_log_id
	 */
	public static function automator_purge_action_logs( $recipe_id, $automator_recipe_log_id ) {
		global $wpdb;
		$action_tbl      = $wpdb->prefix . 'uap_action_log';
		$action_meta_tbl = $wpdb->prefix . 'uap_action_log_meta';
		self::delete_logs( $action_tbl, $action_meta_tbl, 'automator_action_log_id', $recipe_id, $automator_recipe_log_id );
	}

	/**
	 * @param $recipe_id
	 * @param $automator_recipe_log_id
	 */
	public static function automator_purge_closure_logs( $recipe_id, $automator_recipe_log_id ) {
		global $wpdb;
		$closure_tbl      = $wpdb->prefix . 'uap_closure_log';
		$closure_meta_tbl = $wpdb->prefix . 'uap_closure_log_meta';
		self::delete_logs( $closure_tbl, $closure_meta_tbl, 'automator_closure_log_id', $recipe_id, $automator_recipe_log_id );
	}

	/**
	 * @param $tbl
	 * @param $tbl_meta
	 * @param $log_meta_key
	 * @param $recipe_id
	 * @param $automator_recipe_log_id
	 */
	public static function delete_logs( $tbl, $tbl_meta, $log_meta_key, $recipe_id, $automator_recipe_log_id ) {
		global $wpdb;
		$results = $wpdb->get_col( $wpdb->prepare( "SELECT `ID` FROM $tbl WHERE automator_recipe_id=%d AND automator_recipe_log_id=%d", $recipe_id, $automator_recipe_log_id ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( $results ) {
			foreach ( $results as $automator_log_id ) {
				$wpdb->delete(
					$tbl_meta,
					array( $log_meta_key => $automator_log_id )
				);
			}
		}

		$wpdb->delete(
			$tbl,
			array(
				'automator_recipe_id'     => $recipe_id,
				'automator_recipe_log_id' => $automator_recipe_log_id,
			)
		);
	}

	/**
	 *
	 */
	public static function maybe_schedule_purge_logs() {

		if ( ! automator_filter_has_var( '_wpnonce', INPUT_POST ) ) {
			return;
		}

		if ( ! wp_verify_nonce( automator_filter_input( '_wpnonce', INPUT_POST ), 'uncanny_automator' ) ) {
			return;
		}

		as_unschedule_all_actions( self::$cron_schedule );

		//Add Action Scheduler event
		as_schedule_cron_action( strtotime( 'midnight tonight' ), '@daily', self::$cron_schedule );
	}

	/**
	 * Get the URL with the field to prune the logs
	 * 
	 * @return string The URL
	 */
	public function get_logs_settings_url() {
		return add_query_arg(
			array(
				'post_type' => 'uo-recipe',
				'page'      => 'uncanny-automator-config',
				'tab'       => 'general',
				'general'   => 'logs'
			),
			admin_url( 'edit.php' )
		);
	}
}

