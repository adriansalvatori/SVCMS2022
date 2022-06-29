<?php
/**
 * Pages Meta Box
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2016, Andre Gagnon
 * @since       2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )  exit;

/**
 * PH_Meta_Box_Project_Options Class
 *
 * @since 1.0
 */
class PH_Website_Meta_Box_Pages {

	public static $fields = array();

	/**
	 * Output the metabox
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public static function output( $post ) {
		// create nonce field
		wp_nonce_field( 'project_huddle_save_data', 'project_huddle_meta_nonce' ); ?>

		<div id="project_pages_container" class="ph_meta_box"></div>
	<?php }
}