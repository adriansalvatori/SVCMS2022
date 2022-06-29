<?php
/**
 * Project Options Meta Box
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PH_Meta_Box_Project_Options Class
 *
 * @since 1.0
 */
class PH_Meta_Box_Project_Activity {
	/**
	 * Output the metabox
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public static function output( $post ) { ?>
	<script>
	</script>
        <div id="project_activity_container" class="ph_meta_box project-huddle py-2"></div>
		<?php
	}

	/**
	 * Save meta box data
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public static function save( $post_id, $post ) {
	    // do nothing
	}
}