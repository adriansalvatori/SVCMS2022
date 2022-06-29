<?php
/**
 * Send emails in the background
 *
 * Sends emails in a background process to prevent from blocking php thread
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       2.7.3
 */

namespace ph;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the email queue (stored in option)
 *
 * @return array
 */
function get_email_queue() {
	$queue = (array) get_option( 'ph_email_queue', array() );
	$queue = array_filter( $queue );

	return $queue;
}

/**
 * Add to email queue.
 *
 * @param array $args Email args.
 *
 * @return bool
 */
function add_to_email_queue( $args ) {
	$queue   = get_email_queue();
	$queue[] = $args;

	return set_email_queue( $queue );
}

/**
 * Set email queue stored in options
 *
 * @param array $queue Array of email data.
 *
 * @return bool
 */
function set_email_queue( $queue ) {
	return update_option( 'ph_email_queue', $queue );
}

/**
 * Queue mail.
 *
 * @param array $args Email args.
 *
 * @return bool
 */
function queue_wp_mail( $args ) {
	add_to_email_queue( $args );
	// schedule event to process all queued emails.
	if ( ! wp_next_scheduled( 'ph_process_email_queue' ) ) {
		// schedule event to be fired right away.
		wp_schedule_single_event( time(), 'ph_process_email_queue' );
		// send off a request to wp-cron on shutdown.
		add_action( 'shutdown', 'spawn_cron' );
	}

	/**
	 * Return empty `to` and `message` values as this stops the email from being sent
	 *
	 * Once `wp_mail` can be short-circuited using falsey values, we can return false here.
	 *
	 * @see https://core.trac.wordpress.org/ticket/35069
	 */
	return array(
		'to'      => '',
		'message' => '',
	);
}

/**
 * Processes the email queue
 */
function process_email_queue() {
	// remove filter as we don't want to short circuit ourselves.
	remove_filter( 'wp_mail', 'ph\\queue_wp_mail' );

	$queue = get_email_queue();

	if ( ! empty( $queue ) ) {
		// send each queued email.
		foreach ( $queue as $key => $args ) {
			add_filter( 'wp_mail_from_name', '\PH_Mail::get_from_name' );
			add_filter( 'wp_mail_from', '\PH_Mail::get_from_address' );

			wp_mail( $args['to'], $args['subject'], htmlspecialchars_decode( $args['message'] ), $args['headers'], $args['attachments'] );
			unset( $queue[ $key ] );

			remove_filter( 'wp_mail_from_name', '\PH_Mail::get_from_name' );
			remove_filter( 'wp_mail_from', '\PH_Mail::get_from_address' );
		}
		// update queue with removed values.
		set_email_queue( $queue );
	}
}

// processing.
add_action( 'ph_process_email_queue', 'ph\\process_email_queue' );
