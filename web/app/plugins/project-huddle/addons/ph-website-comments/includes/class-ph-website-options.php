<?php
/**
 * Project Options
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PH_Website_Options {
	/**
	 * Gets options used for project
	 *
	 * @since 1.0
	 *
	 * @param int $post_id ID of project
	 *
	 * @return array
	 */
	public function get_project_options( $post_id = 0 ) {
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;

		$options = array(
			// global options
			'global'  => (array) $this->get_global_project_options(),
			// project options
			'project' => array(
				'project_access' => get_post_meta( $post_id, 'ph_project_access', true ),
				'retina_images'  => get_post_meta( $post_id, 'ph_retina', true ),
				'sharing'        => get_post_meta( $post_id, 'ph_project_sharing', true ),
				'tooltip'        => apply_filters( 'ph_comment_tooltip', true, get_post( $post_id ) ),
			),
		);

		return $options;
	}

	public function get_global_project_options() {
		$options = array(
			'avatar_default'            => get_option( 'avatar_default' ),
			'auto_close'                => get_option( 'ph_auto_close', 'on' ) == 'on' ? true : false,
			'ph_image_background_color' => get_option( 'ph_image_bg' ) ? get_option( 'ph_image_bg' ) : false,
		);

		return apply_filters( 'ph_global_website_options', $options );
	}
}
