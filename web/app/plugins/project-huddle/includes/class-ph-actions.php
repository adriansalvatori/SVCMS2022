<?php
/**
 * API Functions
 *
 * Process the API Requests
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PH_Actions {

	/**
	 * Variable to store model data
	 */
	protected $data;

	/**
	 * Setup project admin
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// users
		add_filter( 'ph_get_users', array( $this, 'get_users' ) );
	}

	/**
	 * Reorder pages
	 *
	 * @param array $data Needs ID and ids
	 *
	 * @return mixed
	 */
	public function get_users( $data ) {
		global $wp_roles;
		$this->data = $data->get_params();

		// search for typed fragment wildcard
		$args = array(
			'search' => '*' . $this->data['email'] . '*',
			'number' => 30 // limit to 30
		);
		$users = get_users( $args );

		// add avatars
		foreach ( $users as $user ) {
			$user->avatar = get_avatar( $user->ID, 22 );
			$user->role_display = translate_user_role( $wp_roles->roles[ $user->roles[0] ]['name'] );
		}

		return $users;
	}
}