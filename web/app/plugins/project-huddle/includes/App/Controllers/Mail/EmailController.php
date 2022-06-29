<?php

namespace PH\Controllers\Mail;

use PH\Models\User;
use PH\Models\Thread;
use PH\Controllers\Mail\Mailers\Mailer;

/**
 * Batch Emails Class
 * This class scehdules and sends periodic emails for latest activity between two dates
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

// include actions scheduler
require_once PH_PLUGIN_DIR . 'includes/libraries/action-scheduler/action-scheduler.php';

class EmailController
{
	/**
	 * Store the email interval
	 *
	 * @var String
	 */
	protected $interval;

	/**
	 * Hook into WP
	 */
	public function __construct()
	{
		add_action('ph_settings_email', array($this, 'admin_settings'));
		add_action('admin_init', array($this, 'start_schedule'));

		add_action('update_option_ph_weekly_email', array($this, 'weekly_email_schedule'), 10, 2);
		add_action('add_option_ph_weekly_email', array($this, 'weekly_email_schedule'), 10, 2);

		add_action('update_option_ph_daily_email', array($this, 'daily_email_schedule'), 10, 2);
		add_action('add_option_ph_daily_email', array($this, 'daily_email_schedule'), 10, 2);

		add_action('update_option_ph_email_throttle', array($this, 'activity_email_schedule'), 10, 2);
		add_action('add_option_ph_email_throttle', array($this, 'activity_email_schedule'), 10, 2);

		add_action('ph_website_publish_comment', array($this, 'update_reminder_scheduler'), 10, 2);
		add_action('ph_mockup_publish_comment', array($this, 'update_reminder_scheduler'), 10, 2);

		add_action( 'ph_mockup_rest_create_thread_attribute', array($this, 'assign_reminder_scheduler'), 10, 4 );
		add_action( 'ph_website_rest_create_thread_attribute', array($this, 'assign_reminder_scheduler'), 10, 4 );
		add_action( 'ph_mockup_rest_update_thread_attribute', array($this, 'assign_reminder_scheduler'), 10, 4 );
		add_action( 'ph_website_rest_update_thread_attribute', array($this, 'assign_reminder_scheduler'), 10, 4 );
		add_action( 'ph_send_reminder_process', array($this, 'send_reminder_email'), 10, 1 );
		add_action( 'ph_reminder_schedule_next', array($this, 'ph_reminder_reschedule'), 12, 2 );

		// failed action handling.
		add_action('action_scheduler_failed_action', array($this, 'reschedule'), 10);
		add_action('action_scheduler_failed_execution', array($this, 'reschedule'), 10);
		add_action('action_scheduler_unexpected_shutdown', array($this, 'reschedule'), 10);
		add_action('action_scheduler_failed_fetch_action', array($this, 'reschedule'), 10);
	}

	/**
	 * Reschedule reminder when any new comments added
	 *
	 * @return void
	 */
	public function ph_reminder_reschedule( $thread, $user ) {
		$next_schedule_date = get_post_meta((int) $thread->ID, 'latest_activity_date', true );
		delete_post_meta((int) $thread->ID, 'latest_activity_date' );
		$this->set_reminder_trigger( $thread, $user, $next_schedule_date );
	}

	/**
	 * Update reminder if new comments added by assignee
	 *
	 * @return void
	 */
	public function update_reminder_scheduler( $id, $comment ) {

		//Check if reminder option is enabled from settings page.
		$reminder_enabled = (int) get_option('ph_reminder_email_enable', 0);
		
		if( ! $reminder_enabled ) {
			return;
		}

		$thread_id = $comment->comment_post_ID;
		$commentor_id = $comment->user_id;

		// If assignee has added any new comments.
		if( isset( $thread_id ) && isset( $commentor_id ) ) {
			$assignee_ID = (int) get_post_meta( $thread_id, 'assigned', true);

			if( $assignee_ID == $commentor_id ) {
				$is_scheduled = (int) get_post_meta($thread_id, 'scheduler_id', true);
				if( $is_scheduled ) {
					$comment_date = strtotime( $comment->comment_date_gmt );
					update_post_meta((int) $thread_id, 'latest_activity_date', $comment_date );
				}
			}
		}
		
	}

	/**
	 * Send reminder email
	 *
	 * @return void
	 */
	public function send_reminder_email( $trigger_data ) {

		//Check if reminder option is enabled from settings page.
		$reminder_enabled = (int) get_option('ph_reminder_email_enable', 0);

		if( ! $reminder_enabled ) {
			return;
		}

		$thread_id = $trigger_data['thread_id'];
		$user_id = $trigger_data['user_id'];

		if (!$thread = new Thread($thread_id)) {
			return;
		}

		// Check if reminder is scheduled and latest comment count is same as of the comment count at the time of setting a scheduler.
		if( ! (int) get_post_meta($thread->ID, 'scheduler_id', true) ) {
			return;
		}

		$is_assigned = (int) get_post_meta( $thread->ID, 'assigned', true);
		$is_resolved = (int) get_post_meta( $thread->ID, 'resolved', true);

		// Check if thread is assigned and not resolved.
		if( $is_assigned && ! $is_resolved && ( $user_id == $is_assigned ) ) {

			if (!$user = new User($is_assigned)) {
				return;
			}

			$latest_comment_count = (int) get_post_field('comment_count', $thread->ID );

			if( $latest_comment_count !== $trigger_data['comment_count'] ){
				$action_id = (int) get_post_meta((int) $thread->ID, 'scheduler_id', true);
				if( $action_id ) {
					do_action( 'ph_reminder_schedule_next', $thread, $user );
					return;
				}
			}

			$post_title = get_the_title( $thread_id );
		   
			try {
				(new Mailer('reminder', $thread->projectId()))
					->to($user)
					->template(
						ph_locate_template('email/reminder-email.php'),
						[
							'project_name' => ph_get_the_title($thread->parentsIds()['project']),
							'link'         => ph_email_link($thread->getAccessLink(), __('View Conversation', 'project-huddle')),
							'post_title'   => get_the_title( $thread_id ),
							'username'     => isset( $user->display_name ) ? sanitize_text_field($user->display_name) : sanitize_text_field($user->user_login),
						]
					)
					->subject(apply_filters("ph_project_reminder_email_subject", sprintf(__('Reminder for %1s - %2s', 'project-huddle'), '{{project_name}}', '{{post_title}}'), $is_assigned, $thread))
					->send();
			} catch (Exception $e) {
				// log error but don't crash anything
				error_log($e);
			}

			// Reset post meta for scheduler once action is completed.
			delete_post_meta((int) $thread->ID, 'scheduler_id' );
		}
	}

	/**
	 * Set reminder schedule
	 *
	 * @return void
	 */
	public function assign_reminder_scheduler($attr, $value, $thread, $is_batch) {

		// Check if reminder option is enabled from settings page.
		if( !(int) get_option('ph_reminder_email_enable', 0) ) {
            return;
		}

		// bail if batch editing
        if ($is_batch || !$value) {
            return;
        }

        // must be assigned attribute and have value
        if ('assigned' !== $attr || !isset($value)) {
            return;
        }

        // get thread
        if (!$this->thread = new Thread($thread)) {
            return;
        }

        // get user info
        if (!$this->user = new User($value)) {
            return;
        }

		// Clear comment activity meta data, as we are resetting the scheduler.
		delete_post_meta((int) $this->thread->ID, 'latest_activity_date' );

		$action_id = (int) get_post_meta((int) $this->thread->ID, 'scheduler_id', true);

		// Cancel old scheduled action if a thread is re-assigned to someone else.
		if( $action_id ) {
			\ActionScheduler::store()->cancel_action( $action_id );
		}

		$current_date = gmdate( 'U' );
		$this->set_reminder_trigger( $this->thread, $this->user, $current_date );
		
	}

	/**
	 * Set reminder email trigger
	 *
	 * @return void
	 */
	public function set_reminder_trigger($thread, $user, $schedule_date) {
		$trigger_data = array(
			'thread_id' => $thread->ID,
			'user_id' => $user->ID,
			'comment_count' => (int) get_post_field('comment_count', $thread->ID)
		);

		// Get trigger date from setting page.
		$trigger_time = (int) get_option('ph_reminder_email_trigger');
		$trigger_date = (int) $schedule_date + ( $trigger_time * DAY_IN_SECONDS );
		// $trigger_date = (int) $schedule_date + ( 1 * MINUTE_IN_SECONDS );
		$trigger_date = apply_filters( 'ph_reminder_email_trigger_span', $trigger_date, $schedule_date );

		// Schedule reminder email action.
		$action_id = as_schedule_single_action(
			$trigger_date,
			'ph_send_reminder_process',
			[ $trigger_data ],
			'email'
		);

		// Save the action ID in post meta for future reference.
        if (isset( $thread->ID ) && isset( $action_id )) {
		    update_post_meta((int) $thread->ID, 'scheduler_id', $action_id );
        }

	}

	/**
	 * Start all email schedule
	 *
	 * @return void
	 */
	public function start_schedule()
	{
		if (\get_option('ph_emails_scheduled', false)) {
			return;
		}
		$this->reschedule();
		\update_option('ph_emails_scheduled', true);
	}

	/**
	 * Get the last scheduled action date
	 *
	 * @param String $hook
	 * @param Array|null $args
	 * @param String $group
	 * @return String|Boolean
	 */
	public function get_last_scheduled_action_date($hook, $args = NULL, $group = '')
	{
		$params = array(
			'status' => \ActionScheduler_Store::STATUS_COMPLETE,
		);
		if (is_array($args)) {
			$params['args'] = $args;
		}
		if (!empty($group)) {
			$params['group'] = $group;
		}
		$job_id = \ActionScheduler::store()->find_action($hook, $params);
		if (empty($job_id)) {
			return false;
		}
		$completed_comment = get_comments(array(
			'post_id' => $job_id,
			'type' => 'action_log',
			'number' => 1
		));

		if (!empty($completed_comment)) {
			return $completed_comment[0]->comment_date;
		}
		return false;
	}

	/**
	 * Reschedule failed action
	 */
	public function reschedule()
	{
		// reschedule all on failure
		$this->weekly_email_schedule(false, \get_option('ph_weekly_email', 'on'));
		$this->daily_email_schedule(false, \get_option('ph_daily_email', 'on'));
		$this->activity_email_schedule(false, $this->getSavedInterval());

	}

	/**
	 * Add admin setting
	 *
	 * @param [type] $settings
	 * @return void
	 */
	public function admin_settings($settings)
	{
		$settings['fields']['email_behavior_options']  = array(
			'id'          => 'email_behavior_options',
			'label'       => __('Email Options', 'project-huddle'),
			'description' => '',
			'type'        => 'divider',
		);

		$settings['fields']['email_throttle'] = array(
			'id'          => 'email_throttle',
			'label'       => __('Email Frequency', 'project-huddle'),
			'description' => 'Choose to send immediate emails, or get a single email with all activity included within that time period.',
			'type'        => 'radio',
			'options'     => array(
				'off'         => __('Don\'t send any activity emails automatically.', 'project-huddle'),
				'immediate'   => __('Immediately email subscribed users about each item right away.', 'project-huddle'),
				5   => __('Email a summary every 5 minutes at most.', 'project-huddle'),
				30  => __('Email a summary every 30 minutes at most.', 'project-huddle'),
				180 => __('Email a summary every 3 hours at most.', 'project-huddle'),
			),
			'default'     => 'immediate',
		);

		$settings['fields']['daily_email'] = array(
			'id'          => 'daily_email',
			'label'       => __('Daily Email Summary', 'project-huddle'),
			'description' => __('Send a daily summary email to members of a project.', 'project-huddle'),
			'type'        => 'checkbox',
			'default'     => 'on',
		);

		$settings['fields']['weekly_email'] = array(
			'id'          => 'weekly_email',
			'label'       => __('Weekly Email Summary', 'project-huddle'),
			'description' => __('Send a weekly summary email to members of a project.', 'project-huddle'),
			'type'        => 'checkbox',
			'default'     => 'on',
		);

		$settings['fields']['pass_reset_link'] = array(
			'id'          => 'pass_reset_link',
			'label'       => __('Password Reset Link', 'project-huddle'),
			'description' => __('Send Password reset link via email to the guest users. ( This email will be sent only to the guest users with Project Client role)', 'project-huddle'),
			'type'        => 'checkbox',
			'default'     => 'off',
		);

		$settings['fields']['reminder_email_options'] = array(
			'id'          => 'reminder_email_options',
			'label'       => __('Reminder Emails', 'project-huddle'),
			'description' => '',
			'type'        => 'divider',
		);

		$settings['fields']['reminder_email_enable'] = array(
			'id'          => 'reminder_email_enable',
			'label'       => __('Enable Reminder Emails', 'project-huddle'),
			'description' => __('Enable this to send a reminder email if a conversation is not resolved & unanswered by assignee.', 'project-huddle'),
			'type'        => 'radio',
			'options'     => array(
				1 => __('Yes', 'project-huddle'),
				0 => __('No', 'project-huddle')
			),
			'default'     => 0
		);

		$settings['fields']['reminder_email_trigger'] = array(
			'id'          => 'reminder_email_trigger',
			'label'       => __('If Thread is Unanwsered for', 'project-huddle'),
			'description' => __('Days', 'project-huddle'),
			'type'        => 'number',
			'default'     => '2',
			'required'    => array(
				'reminder_email_enable' => 1
			),
		);

		return $settings;
	}

	/**
	 * Schedules or unschedules a daily email if value is updated
	 *
	 * @param boolean $old_value
	 * @param boolean $value
	 * @return void
	 */
	public function activity_email_schedule($old_value, $value)
	{
		// always change
		\as_unschedule_action('ph_activity_summary_email');

		if ('immediate' === $value || 'off' === $value) {
			return;
		}

		if (false === \as_next_scheduled_action('ph_activity_summary_email') && $value) {
			\as_schedule_recurring_action(strtotime("now"), strtotime($value . ' minutes', 0), 'ph_activity_summary_email', array(), 'email');
		}
	}

	/**
	 * Schedules or unschedules a daily email if value is updated
	 *
	 * @param boolean $old_value
	 * @param boolean $value
	 * @return void
	 */
	public function daily_email_schedule($old_value, $value)
	{
		// unschedule if unchecked
		if (!filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
			as_unschedule_action('ph_daily_summary_email');
			return;
		}

		if (false === as_next_scheduled_action('ph_daily_summary_email')) {
			// allow filtering of day and time
			$time = apply_filters('ph_daily_email_time', '6pm');

			as_schedule_recurring_action(strtotime("$time"), strtotime('1 day', 0), 'ph_daily_summary_email', array(), 'email');
			
		}
	}

	/**
	 * Schedules or unschedules weekly email if value is updated
	 *
	 * @param boolean $old_value
	 * @param boolean $value
	 * @return void
	 */
	public function weekly_email_schedule($old_value, $value)
	{
		// unschedule if unchecked
		if (!filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
			\as_unschedule_action('ph_weekly_summary_email');
			return;
		}

		if (false === \as_next_scheduled_action('ph_weekly_summary_email')) {
			// allow filtering of day and time
			$day  = apply_filters('ph_weekly_email_day', 'Friday');
			$time = apply_filters('ph_weekly_email_time', '6am');

			as_schedule_recurring_action(strtotime("this $day $time"), strtotime('1 week', 0), 'ph_weekly_summary_email', array(), 'email');
		}
	}

	public function getSavedInterval()
	{
		return get_option('ph_email_throttle', 'immediate');
	}

	/**
	 * Get activity interval
	 *
	 * @return void
	 */
	public function get_interval()
	{
		if (!$this->interval) {
			$this->interval = $this->getSavedInterval();
		}
		if ('immediate' === $this->interval || 'off' === $this->interval) {
			return 0;
		}
		return (int) $this->interval;
	}

	/**
	 * Are emails throttled
	 */
	public function is_throttled()
	{
		return $this->get_interval() > 0;
	}

	public function emailsEnabled()
	{
		$this->get_interval();
		return $this->interval !== 'off';
	}
}
