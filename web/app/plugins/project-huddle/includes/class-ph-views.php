<?php
/**
 * View tracking.
 *
 * Tracks Project Views
 *
 * @copyright   Copyright (c) 2015, Andre Gagnon
 *
 * @since       2.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PH_Views {
	/**
	 * Unique views slug.
	 */
	protected $unique = 'ph_unique_views';

	/**
	 * Views slug.
	 */
	protected $views = 'ph_views';

	/**
	 * Setup project admin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp', 'track_views' );
	}

	/**
	 * Track page views.
	 */
	protected function track_views() {
		global $post;

		// only track our post type
		$valid_post_type = in_array( get_post_type( $post ), (array) apply_filters( 'ph_view_track_post_types', array( 'ph-project' ) ) );
		if ( is_admin() || ! $valid_post_type ) {
			return;
		}

		// get all views
		$views = $this->get_views( $post->ID );

		// visitor info
		$user_info = array(
			'user_id' => is_user_logged_in() ? get_current_user_id() : __( 'Anonymous', 'project-huddle' ),
			'ip'      => ph_get_remote_IP(),
			'time'    => current_time( 'mysql' ),
		);

		// update all views
		$views[] = $user_info;
		update_post_meta( $post->ID, 'ph_viewed', $views );

		// track unique views
		if ( ! $_SESSION[ 'ph-seen-' . $post->ID ] ) {
			$unique = $this->get_unique_views( $post->ID );

			if ( is_user_logged_in() ) {
				$unique[] = $user_info;
			}

			update_post_meta( $post->ID, 'ph_unique_viewed', $unique );
			$_SESSION[ 'ph-seen-' . $post->ID ] = true;
		}
	}

	/**
	 * Get all project views.
	 *
	 * @param int $id Post id
	 *
	 * @return array Array of visits
	 */
	public function get_views( $id ) {
		return (array) get_post_meta( $id, $this->views, true );
	}

	/**
	 * Get unique project views.
	 *
	 * @param int $id Post id
	 *
	 * @return array Array of visits
	 */
	public function get_unique_views( $id ) {
		return (array) get_post_meta( $id, $this->unique, true );
	}

	/**
	 * Get view count.
	 */
	public function view_count( $id ) {
		return (int) count( $this->get_views( $id ) );
	}

	/**
	 * Get unique view count.
	 */
	public function unique_view_count( $id ) {
		return (int) count( $this->get_unique_views( $id ) );
	}
}
