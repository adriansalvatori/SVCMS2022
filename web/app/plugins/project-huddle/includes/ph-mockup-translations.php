<?php

/**
 * Translation strings for mockup projects
 */
function ph_mockup_translation_strings()
{
	$translations = array(
		// errors
		'are_you_sure'          => __('Are you sure?', 'project-huddle'),
		'generic_error'         => __('Hmmm... something went wrong. Please try again.', 'project-huddle'),
		'no_comment_text'       => __('You need to enter some comment text.', 'project-huddle'),
		'save_comment_first'    => __('Please save your comment first.', 'project-huddle'),
		'no_images'             => __('No images found.', 'project-huddle'),
		'image_not_founds'      => __('Image doesn\'t exist or cannot be found.', 'project-huddle'),
		'hide_comments'         => __('Hide Comments', 'project-huddle'),
		'show_comments'         => __('Show Comments', 'project-huddle'),
		'comment_placeholder'   => __('Leave A Comment', 'project-huddle'),
		'no_permission'         => __('You don\'t have permission to do that.', 'project-huddle'),
		'email_sent'            => __('Email sent!', 'project-huddle'),
		'login'                 => __('Login', 'project-huddle'),
		'discard'               => __('Discard your comment?', 'project-huddle'),
		'unsaved_changes'       => __('It looks like there are unsaved changes on this project.', 'project-huddle'),
		'tooltip_text'          => __('Click To Leave A Comment', 'project-huddle'),
		'comments_changed'      => __('The comments have changed! You may want to double check your comment again before submitting.', 'project-huddle'),
		'image_slug'            => __('image', 'project-huddle'),
		'confirm_trash_thread'  => __('Are you sure you want to trash this comment thread?', 'project-huddle'),
		'confirm_trash_image'   => __('Are you sure you want to trash this image?', 'project-huddle'),
		'login_required'        => __('Please login to start collaborating!', 'project-huddle'),
		'session_expired'       => __('Your session expired. Please reload the page and try again!', 'project-huddle'),
		'unknown_error'         => __('Something went wrong. Please reload the page and try again!', 'project-huddle'),

		// approval strings
		'approve_image'         => __('Approve Image', 'project-huddle'),
		'unapprove_image'       => __('Unapprove Image', 'project-huddle'),
		'approve'               => __('Approve', 'project-huddle'),
		'approved'              => __('Approved', 'project-huddle'),
		'unapproved'            => __('UnApproved', 'project-huddle'),
		'unapprove'             => __('UnApprove', 'project-huddle'),
		'confirmApprove'        => sprintf(__('Are you sure you want to approve %s?', 'project-huddle'), '<strong>{{item_name}}</strong>'),
		'confirmUnApprove'      => sprintf(__('Are you sure you want to unapprove %s?', 'project-huddle'), '<strong>{{item_name}}</strong>'),
		'approveCheckbox'       => sprintf(__('I, %1$1s, read and agree with the %2$2s.', 'project-huddle'), '{{name}}', '{{terms}}'),
		'approveCheckboxLink'   => __('Terms', 'project-huddle'),
		'approveProject'        => sprintf(__('Also %1$s the other %2$s images in this project.', 'project-huddle'), '{{approval_status}}', '{{image_number}}'),
		'approveTerms'          => '',
		'cant_approve'          => __('You\'re not allowed to approve.', 'project-huddle'),
		'cant_unapprove'        => __('You\'re not allowed to unapprove.', 'project-huddle'),
		'confirm_trash_image'   => __('Are you sure you want to trash this image?', 'project-huddle'),

		'wordpress_users'       => __('WordPress Users', 'project-huddle'),
		'subscribe_people'      => __('Subscribe People To Notifications', 'project-huddle'),
		'no_results'            => __('No users found.', 'project-huddle'),

		'existing_user_email'   => __('That email already exists! Do you want to login?', 'project-huddle'),
		'input_too_short'       => __('Enter an email or search for a user', 'project-huddle'),
		'subscribe_placeholder' => __('Start typing to subscribe someone...', 'project-huddle'),

		'incorrect_password'    => __('That password is incorrect. Please try again.', 'project-huddle'),
		'invalid_username'      => __('It looks like there\'s no account with that username! Perhaps try an email address?', 'project-huddle'),
		'invalid_email'         => __('It looks like there\'s no account with that email address! Perhaps try a username?', 'project-huddle'),
	);

	// add options
	$translations = array_merge($translations, ph_approval_text_options());

	return $translations;
}

/**
 * Add custom approve text to project data
 *
 * @return mixed|void
 */
function ph_approval_text_options()
{
	global $post;

	$translations = array();

	// checkbox/terms
	if ($checkbox_text = get_option('ph_approve_terms_checkbox_text', false)) {
		$translations['approveCheckbox'] = esc_html($checkbox_text);
	}
	if ($checkbox_link = get_option('ph_approve_terms_link_text', false)) {
		$translations['approveCheckboxLink'] = esc_html($checkbox_link);
	}
	if ($terms = get_option('ph_approve_terms', false)) {
		$translations['approveTerms'] = wp_kses_post(wpautop($terms));
	}

	// return translations and filter in case we need to change on a per-post basis
	return apply_filters('ph_approval_text', $translations, $post);
}
