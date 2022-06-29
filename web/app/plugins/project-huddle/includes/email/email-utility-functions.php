<?php
/**
 * Utility functions for emails
 *
 * @since 2.8.0
 * @package ProjectHuddle
 */

/**
 * Turns email link into formatted button
 *
 * @param string $link Link url.
 * @param string $text Link text.
 * @param array  $attrs Inline attributes for the link.
 *
 * @return mixed|void
 */
function ph_email_link( $link, $text, $attrs = array() ) {
	$defaults = array(
		'style' => 'text-align:inherit; margin-top: 35px; margin-bottom: 0;',
	);

	$attrs = wp_parse_args( $attrs, $defaults );

	// force block.
	if ( $attrs['style'] ) {
		$attrs['style'] = $attrs['style'] . 'display:block;';
	}

	$attrs_string = '';

	foreach ( $attrs as $field => $attr ) {
		$attr         = esc_html( $attr );
		$attrs_string = "$field=\"$attr\" ";
	}

	return apply_filters(
		'ph_email_link',
		'<span ' . $attrs_string . '>
		<a bgcolor="' . get_option( 'ph_highlight_color', '#4353ff' ) . '" height="40" href="' . esc_url( $link ) . '" style="display:inline-block;margin:0 auto;background-color:' . get_option( 'ph_highlight_color', '#4353ff' ) . ';color:#fff;min-width:200px;padding:0 25px;text-align:center;text-decoration:none;height:40px;line-height:40px;width:auto;border-radius:3px;font-size:14px; font-family:Helvetica Neue,Helvetica,Arial,sans-serif;" width="auto" target="_blank">
		' . esc_html( $text ) . '</a></span>',
		$link,
		$text,
		$attrs
	);
}


/**
 * Get emails from comment thread
 *
 * @param integer $thread_id
 * @param WP_User $user
 * @return array
 */
function ph_get_thread_emails( $thread_id, $user = false ) {
	// get parents
	$parents = ph_get_parents_ids( $thread_id );

	// get thread member ids
	// these are people who have commented and/or initially reported the issue
	$members_ids = (array) ph_get_thread_member_ids( $thread_id );

	// add the assigned person or notify the project author, if unassigned
	// we can validate this because we will need to get it later and it's stored in cache
	if ( $assigned = get_post_meta( $thread_id, 'assigned', true ) ) {
		if ( get_user_by( 'id', $assigned ) ) {
			$members_ids[] = $assigned;
		}
	}
	$emails = array();

	// If we have user ids.
	if ( ! empty( $members_ids ) ) {
		// Loop through and get emails.
		foreach ( $members_ids as $key => $id ) {
			// don't get your own email
			if ( (int) $id === get_current_user_id() ) {
				continue;
			}

			$member = get_user_by( 'id', $id );

			if ( $member ) {
				$emails[] = sanitize_email( $member->user_email );
			}

			// if user has disabled all email notifications.
			if ( $user && isset( $user->ph_project_email_notifications_disable_all ) ) {
				$disabled = (array) $user->ph_project_email_notifications_disable_all; // Meta key via magic method.

				if ( in_array( $parents['project'], $disabled ) ) {
					unset( $emails[ $key ] );
				}
			}
		}
	}

	// get admin notice option.
	$admin_notice = get_option( 'ph_admin_emails' );

	// email admin if applicable.
	if ( apply_filters( 'ph_admin_notification_emails', $admin_notice ) == 'on' ) {
		$emails[] = get_option( 'admin_email' );
	}

	// return an array of unique email addresses
	return array_filter( array_unique( apply_filters( 'ph_project_emails', $emails, $parents['project'], $user ) ) );
}

/**
 * Get emails to send for new comments, resolving and
 * other actions that need to be scoped
 *
 * Controls the logic of who gets the email
 *
 * @param integer $id Comment Id
 * @param WP_User $user
 * @return array()
 */
function ph_get_comment_thread_emails( $id, $user = false ) {
	if ( ! $user ) {
		$user = wp_get_current_user();
	}

	// get parents ids
	$parents = ph_get_parents_ids( $id, 'comment' );

	// need the project and thread id
	if ( ! $parents['project'] || ! $parents['thread'] ) {
		return array();
	}

	return ph_get_thread_emails( $parents['thread'], $user );
}

/**
 * Gets all email addresses for users on the project
 *
 * @since 1.0.0
 *
 * @param int   $project_id ID of the project.
 * @param mixed $user       If a user array is set, check against list so they don't get their own notification.
 *
 * @return array List of emails
 */
function ph_get_project_emails( $project_id, $user = false ) {
	$ids = ph_get_project_member_ids( $project_id );

	if ( ! $user ) {
		$user = wp_get_current_user();
	}

	// Backwards compatibility.
	if ( empty( $ids ) ) {
		// get backwards compat project member ids.
		$ids = ph_backwards_compat_get_project_member_ids( $project_id );
	}

	// Variable to store our emails.
	$emails = array();

	// If we have user ids.
	if ( ! empty( $ids ) ) {
		// User shouldn't get their own emails.
		$key = array_search( $user->user_id, $ids );
		if ( false !== $key ) {
			unset( $emails[ $key ] );
		}

		// Loop through and get emails.
		foreach ( $ids as $key => $id ) {
			// don't get your own email
			if ( $id === get_current_user_id() ) {
				continue;
			}

			$member = get_user_by( 'id', $id );

			if ( $member ) {
				$emails[] = sanitize_email( $member->user_email );
			}

			// if user has disabled email notifications.
			if ( $user && isset( $user->ph_project_email_notifications_disable_all ) ) {
				$disabled = (array) $user->ph_project_email_notifications_disable_all; // Meta key via magic method.

				if ( in_array( $project_id, $disabled ) ) {
					unset( $emails[ $key ] );
				}
			}
		}
	}

	// get admin notice option.
	$admin_notice = get_option( 'ph_admin_emails' );

	// email admin if applicable.
	if ( apply_filters( 'ph_admin_notification_emails', $admin_notice ) == 'on' ) {
		$emails[] = get_option( 'admin_email' );
	}

	return array_filter( array_unique( apply_filters( 'ph_project_emails', $emails, $project_id, $user ) ) );
}
