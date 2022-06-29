<?php
/**
 * ProjectHuddle Session
 *
 * This is a wrapper class for WP_Session / PHP $_SESSION and handles the storage of access tokens,
 * comment location redirects, etc
 *
 * @package     ProjectHuddle
 * @subpackage  Classes/Session
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// include 4.0 session manager plugin
require_once PH_PLUGIN_DIR . 'includes/libraries/wp-session-manager-4.0/wp-session-manager.php';

/**
 * Lightweight Wrapper for wp-session-manager to auto-prefix and sanitize keys
 *
 * @since 2.7.1
 */
class PH_Session {
	/**
	 * Custom prefix
	 * @var string
	 */
	private $prefix = 'ph_';

	/**
	 * Retrieve a session variable
	 *
	 * @access public
	 * @since 2.7.1
	 * @param string $key Session key
	 * @return mixed Session variable
	 */
	public function get( $key ) {
		if ( ! isset( $_SESSION[ sanitize_key( $this->prefix . $key ) ] ) ) {
			return null;
		}

		return $_SESSION[ sanitize_key( $this->prefix . $key ) ];
	}

	/**
	 * Set a session variable
	 *
	 * @since 2.7.1
	 *
	 * @param string $key Session key
	 * @param int|string|array $value Session variable
	 * @return mixed Session variable
	 */
	public function set( $key, $value ) {
		$_SESSION[ sanitize_key( $this->prefix . $key ) ] = $value;

		return $this->get( $key );
	}

	/**
	 * Set a session variable
	 *
	 * @since 2.7.1
	 *
	 * @param string $key Session key
	 * @param int|string|array $value Session variable
	 * @return mixed Session variable
	 */
	public function clear( $key ) {
		$_SESSION[ sanitize_key( $this->prefix . $key ) ] = false;

		return $this->get( $key );
	}
}