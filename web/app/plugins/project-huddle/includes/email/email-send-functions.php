<?php

/**
 * Email Functions
 *
 * Functions that send specific emails
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Add user to notifications
 *
 * @param integer    $id Id of comment.
 * @param WP_Comment $comment Comment object.
 */
function ph_add_user_to_notifications($id, $comment)
{
	// get author of comment.
	$author = get_user_by('ID', $comment->user_id);

	// need an author.
	if (!$author) {
		return;
	}

	// get ids of parent posts.
	$parents = ph_get_parents_ids($id, 'comment');

	// add as project member.
	ph_add_project_member($parents['project'], $author);
}

add_action('ph_website_publish_comment', 'ph_add_user_to_notifications', 10, 2);
add_action('ph_mockup_publish_comment', 'ph_add_user_to_notifications', 10, 2);
