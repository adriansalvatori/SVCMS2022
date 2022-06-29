<?php

/**
 * Convenience functions for dealing with WP transients
 * Thanks to WebDevStudios: https://webdevstudios.com/2016/07/19/working-transients-like-boss/
 */

/**
 * Delete all transients with a key prefix.
 *
 * @param string $prefix The key prefix.
 */
function ph_delete_transients($prefix)
{
	ph_delete_transients_from_keys(ph_search_database_for_transients_by_prefix('ph_post_count'));
}

/**
 * Searches the database for transients stored there that match a specific prefix.
 *
 * @param  string $prefix Prefix to search for.
 * @return array|bool     Nested array response for wpdb->get_results or false on failure.
 */
function ph_search_database_for_transients_by_prefix($prefix)
{
	global $wpdb;

	// Add our prefix after concating our prefix with the _transient prefix
	$prefix = $wpdb->esc_like('_transient_' . $prefix . '_');

	// Build up our SQL query
	$sql = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";

	// Execute our query
	$transients = $wpdb->get_results($wpdb->prepare($sql, $prefix . '%'), ARRAY_A);

	// If if looks good, pass it back
	if ($transients && !is_wp_error($transients)) {
		return $transients;
	}

	// Otherise return false
	return false;
}

function ph_search_database_for_options_by_prefix($prefix)
{
	global $wpdb;

	// Add our prefix after concating our prefix with the _transient prefix
	$prefix = $wpdb->esc_like($prefix . '_');

	// Build up our SQL query
	$sql = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";

	// Execute our query
	$transients = $wpdb->get_results($wpdb->prepare($sql, $prefix . '%'), ARRAY_A);

	// If if looks good, pass it back
	if ($transients && !is_wp_error($transients)) {
		return $transients;
	}

	// Otherise return false
	return false;
}
/**
 * Expects a passed in multidimensional array of transient keys.
 *
 * array(
 *     array( 'option_name' => '_transient_blah_blah' ),
 *     array( 'option_name' => 'transient_another_one' ),
 * )
 *
 * Can also pass in an array of transient names.
 *
 * @param  array|string $transients  Nested array of transients, keyed by option_name,
 *                                   or array of names of transients.
 * @return array|bool                Count of total vs deleted or false on failure.
 */
function ph_delete_transients_from_keys($transients)
{

	if (!isset($transients)) {
		return false;
	}

	// If we get a string key passed in, might as well use it correctly
	if (is_string($transients)) {
		$transients = array(array('option_name' => $transients));
	}

	// If its not an array, we can't do anything
	if (!is_array($transients)) {
		return false;
	}

	$results = array();

	// Loop through our transients
	foreach ($transients as $transient) {

		if (is_array($transient)) {

			// If we have an array, grab the first element
			$transient = current($transient);
		}

		// Remove that sucker
		$results[$transient] = delete_transient(str_replace('_transient_', '', $transient));
	}

	// Return an array of total number, and number deleted
	return array(
		'total'   => count($results),
		'deleted' => array_sum($results),
	);
}
