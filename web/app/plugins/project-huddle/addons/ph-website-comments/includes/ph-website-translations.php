<?php
/**
 * Website Translation Strings
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ph_website_translation_strings() {
	return apply_filters( 'ph_website_translation_strings', array(
		// errors
		'generic_error'         => __( 'Hmmm... something went wrong. Please try again.', 'project-huddle' ),
		'no_comment_text'       => __( 'You need to enter some comment text.', 'project-huddle' ),
		'save_comment_first'    => __( 'Please save your comment first.', 'project-huddle' ),
		'no_comments'           => __( 'No Comments', 'project-huddle' ),
		'no_pages'              => __( 'No Pages', 'project-huddle' ),
		'hide_comments'         => __( 'Hide Comments', 'project-huddle' ),
		'show_comments'         => __( 'Show Comments', 'project-huddle' ),
		'comment_placeholder'   => __( 'Leave A Comment', 'project-huddle' ),
		'discard'               => __( 'Discard your comment?', 'project-huddle' ),
		'unsaved_changes'       => __( 'It looks like there are unsaved changes on this project.', 'project-huddle' ),
		'tooltip_text'          => __( 'Click To Leave A Comment', 'project-huddle' ),
		'comments_changed'      => __( 'The comments have changed! You may want to double check your comment again before submitting.', 'project-huddle' ),
		'image_slug'            => __( 'image', 'project-huddle' ),
		'discard_comment'       => __( 'Discard your current comment?', 'project-huddle' ),
		'confirmTrash'          => __( 'Are you sure you want to trash', 'project-huddle' ),
		'are_you_sure'          => __( 'Are you sure?', 'project-huddle' ),
		'login_required'        => __( 'You need to login in order to post a comment on this image.', 'project-huddle' ),
		'session_expired'       => __( 'Your session expired. Please reload the page.', 'project-huddle' ),
		'wordpress_users'       => __( 'WordPress Users', 'project-huddle' ),
		'subscribe_people'      => __( 'Subscribe People To Notifications', 'project-huddle' ),
		'no_results'            => __( 'No users found.', 'project-huddle' ),
		'input_too_short'       => __( 'Enter an email or search for a user', 'project-huddle' ),
		'subscribe_placeholder' => __( 'Start typing to subscribe someone...', 'project-huddle' ),
		'all_comments'          => __( 'All Comments', 'project-huddle' ),
		'assigned_to_me'        => __( 'Assigned To Me', 'project-huddle' ),
		'unassigned'            => __( 'UnAssigned', 'project-huddle' ),
		'copied'                => __( 'Copied to clipboard!', 'project-huddle' ),
	) );
}