<?php

/**
 * Settings page settings
 *
 * @package     Project Huddle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Function that holds the array of fields for the settings page
 *
 * @since 1.0.0
 * @return array Fields to be displayed on settings page
 */
function ph_settings_fields()
{

	/**
	 * Filters are provided for each settings section to allow plugins
	 * to add their own settings to an already created section.
	 */
	$settings['customize'] = apply_filters('ph_settings_customize', array(
		'title'  => __('Customize', 'project-huddle'),
		'fields' => array(
			'logo_divider' => array(
				'id'          => 'logo_divider',
				'label'       => __('Logos', 'project-huddle'),
				'description' => '',
				'type'        => 'divider',
			),
			'login_logo' => array(
				'id'          => 'login_logo',
				'label'       => __('Light Logo', 'project-huddle'),
				'description' => __('Logo used on light backgrounds. Appears on login forms and at the top of emails.', 'project-huddle'),
				'type'        => 'image',
				'default'     => '',
				'placeholder' => ''
			),
			'login_logo_retina' => array(
				'id'          => 'login_logo_retina',
				'label'       => __('Retina', 'project-huddle'),
				'description' => __('This will display your logo at half it\'s size.', 'project-huddle'),
				'type'        => 'checkbox',
				'default'     => '',
			),

			'control_logo' => array(
				'id'          => 'control_logo',
				'label'       => __('Dark Logo', 'project-huddle'),
				'description' => __('Logo used on dark backgrounds. Appears on control bars.', 'project-huddle'),
				'type'        => 'image',
				'default'     => '',
				'placeholder' => ''
			),
			'control_logo_retina' => array(
				'id'          => 'control_logo_retina',
				'label'       => __('Retina', 'project-huddle'),
				'description' => __('This will display your logo at half it\'s size.', 'project-huddle'),
				'type'        => 'checkbox',
				'default'     => '',
			),

			/* Highlight Color */
			'colors_divider' => array(
				'id'          => 'colors_divider',
				'label'       => __('Highlight Color', 'project-huddle'),
				'description' => '',
				'type'        => 'divider',
			),
			'highlight_color' => array(
				'id'          => 'highlight_color',
				'label'       => __('Highlight Color', 'project-huddle'),
				'description' => __('Choose a highlight color to match your brand.', 'project-huddle'),
				'type'        => 'color',
				'default'     => '#4353ff'
			),

			'comment_status_divider' => array(
				'id'          => 'comment_status_divider',
				'label'       => __('Comment Status', 'project-huddle'),
				'description' => '',
				'type'        => 'divider',
			),
			'active_status_name' => array(
				'id'          => 'active_status_name',
				'label'       => __('Active Label', 'project-huddle'),
				'description' => __('Enter text to change the status label of Active comments.', 'project-huddle'),
				'default'     => __('Active', 'project-huddle'),
				'type'        => 'text',
				'placeholder'     => __('Active', 'project-huddle'),
			),

			'active_status_color' => array(
				'id'          => 'active_status_color',
				'label'       => __('Active Color', 'project-huddle'),
				'description' => __('Set a color for the Active comments thread-dot and status tag.', 'project-huddle'),
				'type'        => 'color',
				'default'     => get_option('ph_highlight_color'),
			),
			'active_status_divider' => array(
				'id'          => 'active_status_divider',
				'label'       => '',
				'description' => '',
				'type'        => 'divider',
			),

			// Resolve Status
			'resolve_status_name' => array(
				'id'          => 'resolve_status_name',
				'label'       => __('Resolved Status Label', 'project-huddle'),
				'description' => __('Enter text to change the status label of Resolved comments.', 'project-huddle'),
				'type'        => 'text',
				'default'     => __('Resolved', 'project-huddle'),
				'placeholder'     => __('Resolved', 'project-huddle'),
			),

			'resolve_status_color' => array(
				'id'          => 'resolve_status_color',
				'label'       => __('Resolved Status Color', 'project-huddle'),
				'description' => __('Set a color for the Resolved comments thread-dot and status tag.', 'project-huddle'),
				'type'        => 'color',
				'default'     => '#48bb78',
			),
			'resolve_status_divider' => array(
				'id'          => 'resolve_status_divider',
				'label'       => '',
				'description' => '',
				'type'        => 'divider',
			),

			// Progress Status
			'progress_status_enable' => array(
				'id'          => 'progress_status_enable',
				'label'       => __('Hide In-Progress Status', 'project-huddle'),
				'description' => __('Select this option to hide In Progress status.', 'project-huddle'),
				'type'        => 'checkbox',
				'default'     => '',
				'field_class' => 'pin_color_enable_checkbox',
			),

			'progress_status_name' => array(
				'id'          => 'progress_status_name',
				'label'       => __('In-Progress Label', 'project-huddle'),
				'description' => __('Enter text to change the status label of Active comments.', 'project-huddle'),
				'type'        => 'text',
				'default'     => __('In Progress', 'project-huddle'),
				'placeholder'     => __('In Progress', 'project-huddle'),
			),

			'progress_status_color' => array(
				'id'          => 'progress_status_color',
				'label'       => __('In-Progress Status Color', 'project-huddle'),
				'description' => __('Set a color for the In-Progress comments thread-dot and status tag.', 'project-huddle'),
				'type'        => 'color',
				'default'     => '#ffc107',
			),
			'progress_status_divider' => array(
				'id'          => 'progress_status_divider',
				'label'       => '',
				'description' => '',
				'type'        => 'divider',
			),

			// Review Status
			'review_status_enable' => array(
				'id'          => 'review_status_enable',
				'label'       => __('Hide In-Review Status', 'project-huddle'),
				'description' => __('Select this option to hide In Review status.', 'project-huddle'),
				'type'        => 'checkbox',
				'default'     => '',
				'field_class' => 'pin_color_enable_checkbox',
			),

			'review_status_name' => array(
				'id'          => 'review_status_name',
				'label'       => __('In-Review Label', 'project-huddle'),
				'description' => __('Enter text to change the status label of In Review comments.', 'project-huddle'),
				'type'        => 'text',
				'default'     => __('In Review', 'project-huddle'),
				'placeholder'     => __('In Review', 'project-huddle'),
			),

			'review_status_color' => array(
				'id'          => 'review_status_color',
				'label'       =>  __('In-Review Color', 'project-huddle'),
				'description' => __('Set a color for the In-Review comments thread-dot and status tag.', 'project-huddle'),
				'type'        => 'color',
				'default'     => '#ff9800',
			),

			/* Comments */
			'permissions_divider' => array(
				'id'          => 'permissions_divider',
				'label'       => __('Permissions', 'project-huddle'),
				'description' => '',
				'type'        => 'divider',
			),
			'un_silo' => array(
				'id'          => 'un_silo',
				'label'       => __('Universal Project Access', 'project-huddle'),
				'description' => __('Allow Project Client and Project Collaborators to view and access projects they aren\'t subscribed to.', 'project-huddle'),
				'type'        => 'checkbox',
				'default'     => 'on',
			),
		)
	));

	$settings['comment'] = apply_filters('ph_settings_comment', array(
		'title'  => __('Comments', 'project-huddle'),
		'fields' => array(
			'private_comment_devider' => array(
				'id'          => 'private_comment_devider',
				'label'       => __('Private Comment', 'project-huddle'),
				'description' => '',
				'type'        => 'divider',
			),
			/* Private Comments */
			'private_comment_access' => array(
				'id'          => 'private_comment_access',
				'label'       => __('Select User Role', 'project-huddle'),
				'description' => __( 'Allow access to members to view and add private comments.', 'project-huddle' ),
				'type'    => 'select_multi',
				'options'     => get_all_user_role_list(),
				'default'     => array( 'administrator', 'editor', 'project_admin', 'project_editor', 'project_collaborator'  ),
				'field_class'   => 'ph-select2'
			),
			'comment_status_devider' => array(
				'id'          => 'comment_status_devider',
				'label'       => __('Comment Status', 'project-huddle'),
				'description' => '',
				'type'        => 'divider',
			),
			/* Comments Status Access */
			'set_comment_status_access' => array(
				'id'          => 'set_comment_status_access',
				'label'       => __('Comment Status Access', 'project-huddle'),
				'description' => __( 'Select user roles who can change comment status.', 'project-huddle' ),
				'type'        => 'radio',
				'options'     => array(
					0 => __('All', 'project-huddle'),
					1 => __('Custom', 'project-huddle')
				),
				'default'     => 0
			),
			'comment_status_access' => array(
				'id'          => 'comment_status_access',
				'label'       => __('Select User Role', 'project-huddle'),
				'description' => __( 'Select user roles who can change comment status.', 'project-huddle' ),
				'type'    => 'select_multi',
				'options'     => get_all_user_role_list(),
				'default'     => array( 'administrator', 'editor', 'project_admin', 'project_editor', 'project_collaborator'  ),
				'field_class'   => 'ph-select2',
				'required'    => array(
					'set_comment_status_access' => 1
				),
			),
		)
	));

	$settings['approvals'] = apply_filters('ph_settings_approvals', [
		'title'  => __('Approvals', 'project-huddle'),
		'fields' => [
			array(
				'id'          => 'require_terms',
				'label'       => __('Approval Terms &amp; Conditions Checkbox', 'project-huddle'),
				'description' => __('Option to require terms and conditions checkbox on approvals.', 'project-huddle'),
				'type'        => 'radio',
				'options'     => array(
					0 => __('Don\'t require terms agreement', 'project-huddle'),
					1 => __('Require terms agreement', 'project-huddle')
				),
				'default'     => 0
			),
			array(
				'id'          => 'approve_terms_checkbox_text',
				'label'       => __('Approval Terms Checkbox Text', 'project-huddle'),
				'description' => __('Approval Terms Checkbox Text. Use {{terms}} to place the terms link and {{user_name}} to display the current identified user.', 'project-huddle'),
				'type'        => 'text',
				'default'     => sprintf(__('I, %2$s, read and agree with the %1$s.', 'project-huddle'), '{{terms}}', '{{user_name}}'),
				'required'    => array(
					'require_terms' => 1
				)
			),
			array(
				'id'          => 'approve_terms_link_text',
				'label'       => __('Approval Terms Link Text', 'project-huddle'),
				'description' => __('Clickable text to show the terms.', 'project-huddle'),
				'type'        => 'text',
				'default'     => __('Terms', 'project-huddle'),
				'required'    => array(
					'require_terms' => 1
				)
			),
			array(
				'id'          => 'approve_terms',
				'label'       => __('Approval Terms', 'project-huddle'),
				'description' => __('Full Terms and Conditions. HTML Allowed.', 'project-huddle'),
				'type'        => 'textarea',
				'default'     => '',
				'required'    => array(
					'require_terms' => 1
				)
			),
		]
	]);

	$settings['email'] = apply_filters('ph_settings_email', array(
		'title'  => __('Emails', 'project-huddle'),
		'fields' => array(
			'email_from_name' => array(
				'id'          => 'email_from_name',
				'label'       => __('"From" Name', 'project-huddle'),
				'description' => __('This is the name of the email sender.', 'project-huddle'),
				'type'        => 'text',
				'default'     => get_bloginfo('name'),
			),
			'email_from_address' => array(
				'id'          => 'email_from_address',
				'label'       => __('"From" Address', 'project-huddle'),
				'description' => __('This is the email address of the sender.', 'project-huddle'),
				'type'        => 'text',
				'default'     => get_option('admin_email'),
			),
		)
	));

	$settings['advanced'] = apply_filters('ph_settings_advanced', array(
		'title'  => __('Advanced', 'project-huddle'),
		'fields' => array(
			'error_reporting' => array(
				'id'          => 'error_reporting',
				'label'       => __('Send Error Reports', 'project-huddle'),
				'description' => __('Check this box to turn on sending error and reports to ProjectHuddle support.', 'project-huddle'),
				'type'        => 'checkbox',
				'default'     => '',
			),
			'script_debug' => array(
				'id'          => 'script_debug',
				'label'       => __('Turn on script debugging', 'project-huddle'),
				'description' => __('Check this box to turn on helpful script debugging messages.', 'project-huddle'),
				'type'        => 'checkbox',
				'default'     => '',
			),
			'rerun_setup' => array(
				'id'          => 'rerun_setup',
				'label'       => __('Setup Wizard', 'project-huddle'),
				'description' => __('Run the ProjectHuddle setup wizard', 'project-huddle'),
				'type'        => 'button',
				'default'     => admin_url('admin.php?page=ph-setup'),
			),
			'images_trash_bin' => array(
				'id'          => 'images_trash_bin',
				'label'       => __('Restore Trashed Mockup Images', 'project-huddle'),
				'description' => __('Restore trashed images to their original projects.', 'project-huddle'),
				'type'        => 'button',
				'default'     => admin_url('edit.php?post_status=trash&post_type=project_image'),
			),
			'comment_locations_trash_bin' => array(
				'id'          => 'comment_locations_trash_bin',
				'label'       => __('Restore Trashed Mockup Threads', 'project-huddle'),
				'description' => __('Restore trashed comment threads to their original images.', 'project-huddle'),
				'type'        => 'button',
				'default'     => admin_url('edit.php?post_status=trash&post_type=ph_comment_location'),
			),
			'website_comments_trash_bin' => array(
				'id'          => 'website_comments_trash_bin',
				'label'       => __('Restore Trashed Website Threads', 'project-huddle'),
				'description' => __('Restore trashed website comment threads to their original pages.', 'project-huddle'),
				'type'        => 'button',
				'default'     => admin_url('edit.php?post_status=trash&post_type=phw_comment_loc'),
			),
			'website_pages_trash_bin' => array(
				'id'          => 'website_pages_trash_bin',
				'label'       => __('Restore Trashed Website Pages', 'project-huddle'),
				'description' => __('Restore trashed website pages to their original state.', 'project-huddle'),
				'type'        => 'button',
				'default'     => admin_url('edit.php?post_status=trash&post_type=ph-webpage'),
			),
			'script_shielding' => array(
				'id'          => 'script_shielding',
				'label'       => __('Disable Script Shielding', 'project-huddle'),
				'description' => __('Check this box to disable auto-dequeuing of theme styles and scripts on Project and Website pages.', 'project-huddle'),
				'type'        => 'checkbox',
				'default'     => '',
			),
			'use_php_sessions' => array(
				'id'          => 'use_php_sessions',
				'label'       => __('Use Native PHP Sessions', 'project-huddle'),
				'description' => __('Check this box to enable native PHP Sessions (not supported on all servers).', 'project-huddle'),
				'type'        => 'checkbox',
				'default'     => '',
			),
			'uninstall_data_on_delete' => array(
				'id'          => 'uninstall_data_on_delete',
				'label'       => __('Remove All ProjectHuddle Data on Delete?', 'project-huddle'),
				'description' => __('This will remove all data when ProjectHuddle is deleted. This is irreversible!', 'project-huddle'),
				'type'        => 'checkbox',
				'default'     => '',
			)
		)
	));

	$settings['slack_integration'] = apply_filters('ph_settings_slack_integration', array(
		'title'  => __('Slack Integration', 'project-huddle'),
		'fields' => array(

			'slack_enable' => array(
				'id'          => 'slack_terms',
				'label'       => __('Enable Slack Notifications', 'project-huddle'),
				'description' => __('Choose to enable the Slack notifications.', 'project-huddle'),
				'type'        => 'radio',
				'options'     => array(
					1 => __('Yes', 'project-huddle'),
					0 => __('No', 'project-huddle')
				),
				'default'     => 0
			),
			'webhook_url' => array(
				'type' => 'text',
				'label' => __( 'Default Webhook URL', 'Default Webhook URL Label', 'project-huddle' ),
				'id' => 'slack_webhook_default',
				'description' => sprintf(
					__( 'Enter the Slack Webhook URL. You can set up the Webhook URL from %shere%s. For more information refer this %sarticle%s', 'project-huddle' ),
					'<a href="https://api.slack.com/apps/A02QCKQKQ13/incoming-webhooks" target="_blank">','</a>','<a href="https://help.projecthuddle.com/article/153-integrate-projecthuddle-with-slack" target="_blank">','</a>'
					
				),
				'required'    => array(
					'slack_terms' => 1
				),
				'default'     => '',
				'field_class' => 'ph-slack-webhook-default',
			),

			'ph_section_one' => array(
				'type'        => 'divider',
				'label' => __( 'Comments Trigger', 'project-huddle' ),
				'html' => '',
				'id' => 'ph_section_one',
				'description' => '',
				'default'     => '',
				'required'    => array(
					'slack_terms' => 1
				),
			),

			'comment' => array(
				'type' => 'checkbox',
				'id' => 'slack_comment',
				'label' => __('Website/Mockup Comments', 'project-huddle'),
				'description' => __('Notify me when a new comment is added on Websites/Mockups', 'project-huddle'),
				'default'     => '',
				'required'    => array(
					'slack_terms' => 1
				),
			),
			'comment_text' =>array(
				'id'          => 'comment_text',
				'label'       => __('Comment Text', 'project-huddle'),
				'description' => __('Enter the <b>Comment Text</b> for the Slack notification.', 'project-huddle'),
				'type'        => 'textarea',
				'default'     => '{ph_commenter_name} has added a comment on {ph_project_name}',
				'required'    => array(
					'slack_terms' => 1
				),
			),
			'private_comment' => array(
				'type' => 'checkbox',
				'id' => 'private_comment_check',
				'label' => __('Private Comments', 'project-huddle'),
				'description' => __('Notify on Slack when a private comment is added on Websites/Mockups', 'project-huddle'),
				'default'     => '',
				'required'    => array(
					'slack_terms' => 1
				),
			),

			'ph_section_two' => array(
				'type'        => 'divider',
				'label' => __( 'Approval Trigger', 'project-huddle' ),
				'html' => '',
				'id' => 'ph_section_two',
				'description' => '',
				'default'     => '',
				'required'    => array(
					'slack_terms' => 1
				),
			),

			'project_approvals' => array(
				'type' => 'checkbox',
				'id' => 'slack_project_approvals',
				'label' => __('Website/Mockup Approvals', 'project-huddle'),
				'description' =>  __('Notify me when a Website/Mockup is Approved/Unapproved.', 'project-huddle'),
				'default'     => '',
				'required'    => array(
					'slack_terms' => 1
				),
			),
	
			'project_approval_text' =>array(
				'id'          => 'project_approval_text',
				'label'       => __('Approval Text', 'project-huddle'),
				'description' => __('Enter the Website/Mockup <b>Approval Text</b> for the Slack notification', 'project-huddle'),
				'type'        => 'textarea',
				'default'     => '{ph_commenter_name} has {ph_action_status} your {ph_project_type}',
				'required'    => array(
					'slack_terms' => 1
				),
			),

			'ph_section_three' => array(
				'type'        => 'divider',
				'label' => __( 'Resolve Trigger', 'project-huddle' ),
				'html' => '',
				'id' => 'ph_section_three',
				'description' => '',
				'default'     => '',
				'required'    => array(
					'slack_terms' => 1
				),
			),

			'thread_resolves' => array(
				'type' => 'checkbox',
				'id' => 'slack_thread_resolves',
				'label' => __('Resolve Actions', 'project-huddle'),
				'description' =>  __('Notify me when a conversation thread is Resolved/Unresolved.', 'project-huddle'),
				'default'     => '',
				'required'    => array(
					'slack_terms' => 1
				),
			),
			'project_resolve_text' =>array(
				'id'          => 'project_resolve_text',
				'label'       => __('Resolve Text', 'project-huddle'),
				'description' => __('Enter the threads <b>Resolve Text</b> for the Slack notification', 'project-huddle'),
				'type'        => 'textarea',
				'default'     => '{ph_commenter_name} has {ph_action_status} a conversation',
				'required'    => array(
					'slack_terms' => 1
				),
			),
			'shortcode_desp' =>array(
				'id'          => 'shortcode_desp',
				'label'       => __('Shortcodes', 'project-huddle'),
				'type' => 'divider',
				'description' => __('Use the following shortcodes to customize your message.<span class="ph_short_desp"><p>{ph_project_name} - The project name.</p><p>{ph_commenter_name} - The commenter name.</p><p>{ph_action_status} - This will return the project approved/unapproved, comment resolved/unresolved status as per the action.</p> <p>{ph_project_type} - Returns the project type (Mockup/Website).</p></span>', 'project-huddle'),
				'required'    => array(
					'slack_terms' => 1
				),
			),
		),
	));

	// allow filter of fields
	$settings = apply_filters('project_huddle_settings_fields', $settings);

	return $settings;
}

function get_all_user_role_list() {
	$roles = (array) get_editable_roles();
	$roles_array = array();

	if ( ! empty( $roles ) ) {
		foreach ( $roles as $slug => $role ) {
			$roles_array[$slug] = $role['name'];
		}
	}
	return $roles_array;
}

/**
 * Hide updates field for subsites
 *
 * @param $settings
 *
 * @return mixed
 */
function ph_hide_settings_fields_for_subsites($settings)
{
	if (is_multisite() && !is_main_site()) {
		unset($settings['updates']);
	}
	return $settings;
}
add_filter('project_huddle_settings_fields', 'ph_hide_settings_fields_for_subsites', 20);

/**
 * Empty extensions tab
 * @param $extensions
 *
 * @return mixed
 */
function ph_empty_extensions($extensions)
{
	if (empty($extensions['fields'])) {
		$extensions['fields'] = array(
			/* Comments */
			'no_extensions' => array(
				'id'          => 'no_extensions',
				'label'       => __('No extensions installed.', 'project-huddle'),
				'description' => '',
				'html'        => __('Please stay tuned for available extensions!', 'project-huddle'),
				'type'        => 'custom',
			),
		);
	}

	return $extensions;
}

add_filter('ph_settings_extensions', 'ph_empty_extensions', 9999);

// Private Comments accessibilty option.
add_filter('ph_check_private_comments_access', function () {  
	$is_accessible = 0;

	if( function_exists('is_user_logged_in') && is_user_logged_in() ) {
		$ph_roles = get_option('ph_private_comment_access', false);
		$ph_roles_array = is_array( $ph_roles ) ? $ph_roles : array();
		$user = wp_get_current_user();
		$user_roles = is_array( $user->roles ) ? $user->roles : array();
		$accessible = array_intersect( $ph_roles_array, $user_roles );
		$is_accessible = ( is_array( $accessible ) && 0 !== sizeof( $accessible ) ) ? 1 : 0;
	}
	
	return $is_accessible;
}, 9);

// Comment status  accessibilty option
add_filter('ph_comments_status_role_access', function () {
	$is_accessible = 0;

	if( function_exists('is_user_logged_in') && is_user_logged_in() ) {
		$ph_roles = get_option('comment_status_access', false);
		$ph_roles_array = is_array( $ph_roles ) ? $ph_roles : array();
		$user = wp_get_current_user();
		$user_roles = is_array( $user->roles ) ? $user->roles : array();
		$accessible = array_intersect( $ph_roles_array, $user_roles );
		$is_accessible = ( is_array( $accessible ) && 0 !== sizeof( $accessible ) ) ? 1 : 0;
	}
	
	return $is_accessible;
}, 9);
