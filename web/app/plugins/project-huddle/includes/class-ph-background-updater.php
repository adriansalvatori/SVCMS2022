<?php

/**
 * Background Updater
 *
 * @version  2.6.0
 * @package  WooCommerce/Classes
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WP_Background_Process', false)) {
	require_once PH_PLUGIN_DIR . 'includes/libraries/class-wp-background-process.php';
}

/**
 * PH_Background_Updater Class.
 */
class PH_Background_Updater extends WP_Background_Process
{

	/**
	 * Initiate new background process.
	 */
	public function __construct()
	{
		// Uses unique prefix per blog so each blog has separate queue.
		$this->prefix = 'wp_' . get_current_blog_id();
		$this->action = 'ph_updater';

		parent::__construct();
	}

	/**
	 * Dispatch updater.
	 *
	 * Updater will still run via cron job if this fails for any reason.
	 */
	public function dispatch()
	{
		$dispatched = parent::dispatch();

		ph_log('Updater for ' . PH_VERSION . ' dispatched.');
		if (is_wp_error($dispatched)) {
			ph_log($dispatched, 'error');
		}
	}

	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 */
	public function handle_cron_healthcheck()
	{
		if ($this->is_process_running()) {
			// Background process already running.
			return;
		}

		if ($this->is_queue_empty()) {
			// No data to process.
			$this->clear_scheduled_event();
			return;
		}

		$this->handle();
	}

	/**
	 * Schedule fallback event.
	 */
	protected function schedule_event()
	{
		if (!wp_next_scheduled($this->cron_hook_identifier)) {
			wp_schedule_event(time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier);
		}
	}

	/**
	 * Is the updater running?
	 *
	 * @return boolean
	 */
	public function is_updating()
	{
		return false === $this->is_queue_empty();
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param string $callback Update callback function.
	 * @return mixed
	 */
	protected function task($callback)
	{
		if (!defined('PH_UPDATING')) {
			define('PH_UPDATING', true);
		}

		require_once PH_PLUGIN_DIR . 'includes/update.php';

		if (is_callable($callback)) {
			ph_log(sprintf('Running %s callback', $callback));
			call_user_func($callback);
			ph_log(sprintf('Finished %s callback', $callback));
		} else {
			ph_log(sprintf('Could not find %s callback', $callback));
		}

		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete()
	{
		$roles = new PH_Roles();
		$roles->add_roles();
		$roles->add_caps();

		// store license data.
		ph_store_license_data();

		/* Restore original Post Data */
		wp_reset_postdata();

		// flush rewrite rules.
		add_action('shutdown', function () {
			flush_rewrite_rules();
		});

		// update option.
		update_site_option('ph_db_version', PH_VERSION);

		ph_log('Permalinks and roles flushed as part of ' . PH_VERSION . ' update.');
		ph_log('Version ' . PH_VERSION . ' update complete.');

		// call parent complete
		parent::complete();
	}
}
