<?php
require_once PH_PLUGIN_DIR . 'includes/libraries/wp-session-manager/wp-session-manager.php';

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
		$wp_session = WP_Session::get_instance();

		if ( ! isset( $wp_session[ sanitize_key( $this->prefix . $key ) ] ) ) {
			return null;
		}

		return $wp_session[ sanitize_key( $this->prefix . $key ) ];
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
		$wp_session = WP_Session::get_instance();

		$wp_session[ sanitize_key( $this->prefix . $key ) ] = $value;

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
		$wp_session = WP_Session::get_instance();

		$wp_session[ sanitize_key( $this->prefix . $key ) ] = false;

		return $this->get( $key );
	}
}