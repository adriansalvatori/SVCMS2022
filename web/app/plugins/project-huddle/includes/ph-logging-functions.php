<?php
if ( ! class_exists('PH_Logger') ) {
	require_once PH_PLUGIN_DIR . 'includes/logger/ph-logger.php';
}

/**
 * @param string $message  Message to log (required)
 * @param string $type     Type of log message (defaults to debug)
 * @param string $context  Context (optional)
 * @param int    $severity Severity (optional)
 */
function ph_log($message = '', $type = 'debug', $context = 'project-huddle', $severity = 0 ) {
	if ( ! $message ) {
		return;
	}

	if ( !is_string($message)) {
		$message = print_r($message, 1);
	}

	$types = array(
		'debug', // Detailed debug information
		'info', // Interesting events
		'notice', // Normal but significant events
		'warning', // Exceptional occurrences that are not errors
		'error', // Runtime errors that do not require immediate attention
		'critical', // Critical conditions
		'alert', // Action must be taken immediately
		'emergency' // System is unusable
	);

	if ( ! $key = array_search( $type, $types ) ) {
		$type = 'debug';
	}

	if ( ! $severity && $key) {
		$severity =  $key;
	}

	// do log action
	do_action( 'ph_logger_add', $context, $type, $message, $severity );
}

function ph_start_log_session($message = '', $type = 'debug', $context = 'project-huddle', $severity = 0) {
	do_action( 'wp_logger_create_session', $context, $type, $message, $severity );
}

function ph_end_log_session() {
	// Explicitly end the current session.
	do_action( 'wp_logger_end_session' );
}