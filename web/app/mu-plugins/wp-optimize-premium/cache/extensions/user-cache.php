<?php

if (!defined('ABSPATH')) die('No direct access allowed');

require_once ABSPATH. WPINC . '/pluggable.php';

// Add filter for storing wp_salt() values to cache config
add_filter('wpo_cache_update_config', 'wpo_cache_add_salt_to_config');

// Add filter for filtering user specific cache filename.
if (!empty($GLOBALS['wpo_cache_config']['enable_user_specific_cache'])) {
	add_filter('wpo_cache_filename', 'wpo_user_cache_filename');
}

/**
 * Get username from WordPress cookies.
 *
 * @return string|bool
 */
if (!function_exists('wpo_username_from_cookies')) :
function wpo_username_from_cookies() {

	global $wpdb;

	// initialize database if it isn't initialized.
	if (!$wpdb) {
		require_wp_db();
		wp_set_wpdb_vars();
	}

	// if salt value doesn't exist in configuration the return.
	if (empty($GLOBALS['wpo_cache_config']['wp_salt_logged_in'])) return false;

	// get wordpress_logged_in cookie values.
	$cookie_parts = array();
	foreach ($_COOKIE as $key => $value) {
		if (!preg_match('/^wordpress_logged_in_.+/', $key)) continue;

		$cookie_parts = explode('|', $value);
	}

	if (4 > count($cookie_parts)) return false;

	list($username, $expiration, $token, $hmac) = $cookie_parts;

	// if cookies expired then return false.
	if ($expiration < time()) return false;

	// get user password hash from database.
	$user = $wpdb->get_row($wpdb->prepare("SELECT user_pass FROM {$wpdb->users} WHERE user_login=%s", $username));

	// if used doesn't exist return false.
	if (!is_object($user)) return false;

	// validate cookie hash, used code from:
	// wp_validate_auth_cookie() wp-includes/pluggable.php

	$pass_frag = substr($user->user_pass, 8, 4);

	$key = hash_hmac('md5', $username . '|' . $pass_frag . '|' . $expiration . '|' . $token, $GLOBALS['wpo_cache_config']['wp_salt_logged_in']);

	$algo = function_exists('hash') ? 'sha256' : 'sha1';
	$hash = hash_hmac($algo, $username . '|' . $expiration . '|' . $token, $key);

	if (!hash_equals($hash, $hmac)) return false; // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.hash_equalsFound

	return $username;
}
endif;

if (!function_exists('wpo_user_cache_filename')) :

/**
 * Filters cached filename.
 *
 * @param string $filename source filename
 *
 * @return string
 */
function wpo_user_cache_filename($filename) {
	
	if (!wpo_cache_loggedin_users()) return $filename;

	$username = wpo_username_from_cookies();
	
	if (!$username) return $filename;

	// if salt value doesn't exist in configuration the return.
	if (empty($GLOBALS['wpo_cache_config']['wp_salt_auth'])) return $filename;
	
	$salt = $GLOBALS['wpo_cache_config']['wp_salt_auth'];

	$encoded_filename = sha1($filename.$salt.$username);
	
	return $encoded_filename;
}

endif;

if (!function_exists('wpo_cache_add_salt_to_config')) :

/**
 * Add required wp_salt() values to the cache config.
 *
 * @param array $config
 *
 * @return array
 */
function wpo_cache_add_salt_to_config($config) {
	$config['wp_salt_auth'] = wp_salt('auth');
	$config['wp_salt_logged_in'] = wp_salt('logged_in');
	return $config;
}
endif;

/**
 * Filter cache filename and add current user role info to the filename.
 */
if (!function_exists('wpo_per_role_cache_filename')) :

function wpo_per_role_cache_filename($filename) {
	$username = wpo_username_from_cookies();
	
	if (!$username) return $filename;

	// get selected roles in configuration.
	$selected_roles = !empty($GLOBALS['wpo_cache_config']['per_role_cache']) ? $GLOBALS['wpo_cache_config']['per_role_cache'] : array();
	// get current user roles
	$roles = wpo_get_user_roles($username);

	$roles = array_intersect($selected_roles, $roles);
	sort($roles);

	if (empty($roles || empty($GLOBALS['wpo_cache_config']['wp_salt_auth']))) return $filename;

	$salt = $GLOBALS['wpo_cache_config']['wp_salt_auth'];

	$filename = $filename . sha1($filename.$salt.join('|', $roles));

	return $filename;
}
endif;

/**
 * Get user roles by username ordered.
 *
 * @param string $username
 * @return array
 */
if (!function_exists('wpo_get_user_roles')) :

function wpo_get_user_roles($username) {
	global $wpdb;

	static $user_roles = array();

	if (array_key_exists($username, $user_roles)) return $user_roles[$username];

	// initialize database if it isn't initialized.
	if (!$wpdb) {
		require_wp_db();
		wp_set_wpdb_vars();
	}

	$user = $wpdb->get_row($wpdb->prepare("SELECT `ID` FROM {$wpdb->users} WHERE `user_login`=%s", $username));
	if (empty($user)) return false;

	$roles_value = $wpdb->get_row($wpdb->prepare("SELECT `meta_value` FROM {$wpdb->usermeta} WHERE `meta_key`='wp_capabilities' AND `user_id`=%s", $user->ID));
	if (empty($roles_value)) return false;

	$roles = unserialize($roles_value->meta_value);

	$result = array();

	foreach ($roles as $role => $enabled) {
		if ($enabled) $result[] = $role;
	}

	$user_roles[$username] = $result;

	return $result;
}

endif;

/**
 * Check if we currently cache per roles.
 */
if (!function_exists('wpo_we_cache_per_role')) :

function wpo_we_cache_per_role() {

	// we don't cache per role when option disabled
	if (empty($GLOBALS['wpo_cache_config']['enable_per_role_cache']) || empty($GLOBALS['wpo_cache_config']['wp_salt_auth'])) return false;

	$logged_in = false;
	$wp_cookies = array('wordpressuser_', 'wordpresspass_', 'wordpress_sec_', 'wordpress_logged_in_');

	foreach ($_COOKIE as $key => $value) {
		foreach ($wp_cookies as $cookie) {
			if (0 === strpos($key, $cookie)) {
				$logged_in = true;
				break(2);
			}
		}
	}

	// we don't cache per role when user in not logged-in
	if (!$logged_in) return false;
	
	$username = wpo_username_from_cookies();
	
	// we don't use per role cache when we can't get username from cookies
	if (!$username) return false;

	// get selected roles in configuration.
	$selected_roles = !empty($GLOBALS['wpo_cache_config']['per_role_cache']) ? $GLOBALS['wpo_cache_config']['per_role_cache'] : array();

	// get current user roles
	$roles = wpo_get_user_roles($username);
	$roles = array_intersect($selected_roles, $roles);

	// we don't cache per role when user has no roles selected in cache settings
	if (empty($roles)) return false;

	return true;
}

endif;

/**
 * Add cache filename filter when we cache per role
 */
if (wpo_we_cache_per_role()) {
	add_filter('wpo_cache_filename', 'wpo_per_role_cache_filename', 1);
}
