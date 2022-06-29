<?php
/**
 * Template Functions
 *
 * Functions for the templating system.
 *
 * @package     ProjectHuddle
 * @subpackage  ProjectHuddle File Uploads
 * @copyright   Copyright (c) 2018, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Output backbone templates.
 *
 * @return void
 */
function phf_upload_templates() {
	ph_get_template( 'comment/file-attachment-thumb.tmpl.php', '', '', PH_UPLOADS_PLUGIN_DIR . 'templates/' );
	ph_get_template( 'comment/comment-file-attachment-thumb.tmpl.php', '', '', PH_UPLOADS_PLUGIN_DIR . 'templates/' );
}
add_action( 'ph_website_header', 'phf_upload_templates', 20 );
add_action( 'ph_underscore_templates', 'phf_upload_templates', 20 );
add_action( 'admin_footer', 'phf_upload_templates', 20 );

function phf_upload_mockup_templates() {
	ph_get_template( 'comment/file-upload.tmpl.php', '', '', PH_UPLOADS_PLUGIN_DIR . 'templates/' );

}
add_action( 'ph_underscore_templates', 'phf_upload_mockup_templates', 20 );

/**
 * Output attachment container
 *
 * @return void
 */
function phf_attachment_container() {?>
	<div class="ph-attachment-container"></div>
	<?php
}
add_action( 'ph_thread_content', 'phf_attachment_container', 41 );

/**
 * Output comment attachment container on comment items
 *
 * @return void
 */
function phf_comment_attachments() {
	?>
	<div class="ph-comment-attachment-container"></div>
	<?php
}

add_action( 'ph_comment_item_after_text', 'phf_comment_attachments' );

function ph_file_input_types() {
	$allowed    = get_allowed_mime_types();
	$types      = implode( ',', $allowed );
	$extensions = implode( ',', array_keys( $allowed ) );

	return apply_filters( 'ph_file_input_types', $types . $extensions );
}
