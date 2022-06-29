<?php

/**
 * Roles and Capabilities
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
 * PH_Roles Class
 *
 * Handles roles and permissions for projects.
 *
 * @since 1.0.0
 */
class PH_Roles
{
	/**
	 * PH_Roles constructor.
	 */
	public function __construct()
	{
		add_action('admin_menu', array($this, 'redirect_post_type_publish'));
		add_action('admin_head', array($this, 'hide_buttons'));
	}

	/**
	 * Add new project roles with default WP caps
	 *
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function add_roles()
	{
		add_role(
			'project_admin',
			__('Project Administrator', 'project-huddle'),
			array(
				'read'              => true, // edit their profile
				'moderate_comments' => true, // edit others comments
				'edit_comment_meta' => true,
				'upload_files'      => true,
			)
		);

		add_role(
			'project_editor',
			__('Project Editor', 'project-huddle'),
			array(
				'read'              => true, // edit their profile
				'moderate_comments' => false, // edit others comments
				'edit_comment_meta' => true,
				'upload_files'      => true,
			)
		);

		add_role(
			'project_collaborator',
			__('Project Collaborator', 'project-huddle'),
			array(
				'read'              => true, // edit their profile
				'moderate_comments' => false, // edit others comments
				'list_users'        => true,
				'edit_comment_meta' => true,
				'upload_files'      => true,
			)
		);

		add_role(
			'project_client',
			__('Project Client', 'project-huddle'),
			array(
				'read'              => true, // edit their profile
				'moderate_comments' => false, // edit others comments
				'edit_comment_meta' => true,
				'upload_files'      => true,
			)
		);
	}

	/**
	 * Add new project-specific capabilities
	 *
	 * @access public
	 * @since  1.4.4
	 * @global WP_Roles $wp_roles
	 * @return void
	 */
	public function add_caps()
	{
		global $wp_roles;

		if (class_exists('WP_Roles')) {
			if (!isset($wp_roles)) {
				$wp_roles = new WP_Roles();
			}
		}

		if (is_object($wp_roles)) {
			// settings
			$wp_roles->add_cap('administrator', 'manage_project_settings');
			$wp_roles->add_cap('administrator', 'manage_ph_settings');
			$wp_roles->add_cap('project_admin', 'manage_ph_settings');

			// Project clients can login with an access token
			$wp_roles->add_cap('project_client', 'login_with_access_token');

			// client caps, everyone gets these!
			$capabilities = $this->get_client_caps();
			foreach ($capabilities as $cap_group) {
				foreach ($cap_group as $cap) {
					$wp_roles->add_cap('administrator', $cap);
					$wp_roles->add_cap('project_admin', $cap);
					$wp_roles->add_cap('project_editor', $cap);
					$wp_roles->add_cap('project_collaborator', $cap);
					$wp_roles->add_cap('project_client', $cap);

					// default WordPress roles
					$wp_roles->add_cap('editor', $cap);
					$wp_roles->add_cap('author', $cap);
					$wp_roles->add_cap('collaborator', $cap);
					$wp_roles->add_cap('subscriber', $cap);
				}
			}

			// make extra sure these users can't delete others
			$wp_roles->remove_cap('project_client', 'delete_others_phw_comment_locs');

			// Add the main post type capabilities
			$capabilities = $this->get_core_caps();
			foreach ($capabilities as $cap_group) {
				foreach ($cap_group as $cap) {
					$wp_roles->add_cap('administrator', $cap);
					$wp_roles->add_cap('project_admin', $cap);
					$wp_roles->add_cap('project_editor', $cap);
				}
			}

			// collaborator
			$capabilities = $this->get_edit_own_caps();
			foreach ($capabilities as $cap_group) {
				foreach ($cap_group as $cap) {
					$wp_roles->add_cap('project_collaborator', $cap);
				}
			}

			$wp_roles->remove_cap('administrator', 'login_with_access_token');
			$wp_roles->remove_cap('project_admin', 'login_with_access_token');
			$wp_roles->remove_cap('project_editor', 'login_with_access_token');
			$wp_roles->remove_cap('project_collaborator', 'login_with_access_token');
		}
	}

	public function get_client_caps()
	{
		$capabilities = array();

		// child post types (pages/images & threads)
		$child_types = (array) ph_get_child_post_types();
		foreach ($child_types as $capability_type) {
			$capabilities[$capability_type] = array(
				"publish_{$capability_type}s",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
				'read', // edit their profile
				'edit_comment_meta',
				'upload_files', // allow uploading of files (will limit to their own for subscribers and clients)
			);
		}

		// parent post types (websites/mockups)
		$post_types = ph_get_post_types();
		foreach ($post_types as $capability_type) {
			$capabilities[$capability_type] = array(
				"read_{$capability_type}",
				"read_{$capability_type}s",
				"read_private_{$capability_type}s",
			);
		}

		return apply_filters('ph_project_client_capabilities', $capabilities);
	}

	/**
	 * Gets the core post type capabilities
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array $capabilities Core post type capabilities
	 */
	public function get_core_caps()
	{
		$capabilities = array();

		$capability_types = ph_get_post_types();
		foreach ($capability_types as $capability_type) {
			$capabilities[$capability_type] = array(
				// Post type
				"create_{$capability_type}s",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
				"view_unsubscribed_{$capability_type}s",

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",

				// can list users
				'list_users',
			);
		}

		// child post types (pages/images & threads)
		$child_types = (array) ph_get_child_post_types();
		foreach ($child_types as $capability_type) {
			$capabilities[$capability_type] = array(
				"delete_others_{$capability_type}s",
			);
		}

		return $capabilities;
	}

	public function get_edit_own_caps()
	{
		$capabilities = array();

		$capability_types = ph_get_post_types();

		foreach ($capability_types as $capability_type) {
			$capabilities[$capability_type] = array(
				"edit_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
			);
		}

		return $capabilities;
	}

	public function get_edit_others_caps()
	{
		$capabilities = array();

		$capability_types = ph_get_post_types();

		foreach ($capability_types as $capability_type) {
			$capabilities[$capability_type] = array(
				"edit_others_{$capability_type}s",
				"delete_others_{$capability_type}s",
			);
		}

		return $capabilities;
	}

	public function get_light_caps()
	{
		$capabilities = array();

		$capability_types = ph_get_post_types();

		foreach ($capability_types as $capability_type) {
			$capabilities[$capability_type] = array(
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",
			);
		}

		return $capabilities;
	}

	/**
	 * Remove core post type capabilities (called on uninstall)
	 *
	 * @access public
	 * @since  2.0.0
	 * @return void
	 */
	public function remove_caps()
	{
		global $wp_roles;

		if (class_exists('WP_Roles')) {
			if (!isset($wp_roles)) {
				$wp_roles = new WP_Roles();
			}
		}

		if (is_object($wp_roles)) {
			$wp_roles->remove_cap('project_admin', 'manage_project_settings');
			$wp_roles->remove_cap('administrator', 'manage_project_settings');
			$wp_roles->remove_cap('administrator', 'manage_ph_settings');

			// client caps, everyone gets these!
			$capabilities = $this->get_client_caps();
			foreach ($capabilities as $cap_group) {
				foreach ($cap_group as $cap) {

					// don't remove core caps
					if (in_array(
						$cap,
						array(
							'read', // edit their profile
							'edit_comment_meta',
							'list_users',
							'upload_files', // allow uploading of files (will limit to their own for subscribers and clients)
						)
					)) {
						continue;
					}

					$wp_roles->remove_cap('administrator', $cap);
					$wp_roles->remove_cap('project_admin', $cap);
					$wp_roles->remove_cap('project_editor', $cap);
					$wp_roles->remove_cap('project_collaborator', $cap);
					$wp_roles->remove_cap('project_client', $cap);

					// default WordPress roles
					$wp_roles->remove_cap('editor', $cap);
					$wp_roles->remove_cap('author', $cap);
					$wp_roles->remove_cap('collaborator', $cap);
					$wp_roles->remove_cap('subscriber', $cap);
				}
			}

			$wp_roles->remove_cap('administrator', 'manage_project_settings');
			$wp_roles->remove_cap('administrator', 'manage_ph_settings');
			$wp_roles->remove_cap('project_admin', 'manage_ph_settings');
			$wp_roles->remove_cap('project_client', 'login_with_access_token');

			/** Remove the Main Post Type Capabilities */
			$capabilities = $this->get_core_caps();
			foreach ($capabilities as $cap_group) {
				foreach ($cap_group as $cap) {
					// don't remove core caps
					if (in_array(
						$cap,
						array(
							'read', // edit their profile
							'edit_comment_meta',
							'list_users',
							'upload_files', // allow uploading of files (will limit to their own for subscribers and clients)
						)
					)) {
						continue;
					}

					$wp_roles->remove_cap('project_admin', $cap);
					$wp_roles->remove_cap('project_editor', $cap);
					$wp_roles->remove_cap('administrator', $cap);
					$wp_roles->remove_cap('editor', $cap);
				}
			}

			// client
			$post_types = ph_get_post_types();
			foreach ($post_types as $type) {
				$wp_roles->remove_cap('project_client', "read_ph-{$type}");
				$wp_roles->remove_cap('project_client', "read_private_ph-{$type}s");
			}
			$wp_roles->remove_cap('project_client', 'upload_files');

			$capabilities = $this->get_light_caps();
			foreach ($capabilities as $cap_group) {
				foreach ($cap_group as $cap) {
					$wp_roles->remove_cap('project_collaborator', $cap);
				}
			}
		}
	}

	/**
	 * Cause error on project publish link if user can't
	 */
	public function redirect_post_type_publish()
	{
		$post_types = ph_get_post_types();
		foreach ($post_types as $type) {
			$result = stripos($_SERVER['REQUEST_URI'], "post-new.php?post_type={$type}");
			if ($result !== false && !current_user_can("publish_{$type}s")) {
				wp_redirect(get_option('siteurl') . '/wp-admin/index.php?permissions_error=true');
			}
		}
	}

	/**
	 * Hide buttons for specific roles
	 */
	function hide_buttons()
	{
		global $current_screen;

		$post_types = ph_get_post_types();
		foreach ($post_types as $type) {
			if ($current_screen && $current_screen->id == "edit-{$type}" && !current_user_can("publish_{$type}s")) {
				echo '<style>.page-title-action{display: none;}</style>';
			}
		}
	}
}

/**
 * Add Roles to new blog
 *
 * @param $blog_id
 * @param $user_id
 * @param $domain
 * @param $path
 * @param $site_id
 * @param $meta
 */
function ph_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta)
{
	if (is_plugin_active_for_network('project-huddle/project-huddle.php')) {
		switch_to_blog($blog_id);
		$roles = new PH_Roles();
		$roles->add_roles();
		$roles->add_caps();
		restore_current_blog();
	}
}

add_action('wpmu_new_blog', 'ph_new_blog', 10, 6);

/**
 * Hide admin bar for users without edit permissions of ProjectHuddle
 */
function ph_hide_client_admin_bar()
{
	if (is_admin()) {
		return;
	}
	if (apply_filters('ph_disable_admin_bar', true) && !(current_user_can('edit_posts') || current_user_can('edit_ph-projects') || current_user_can('edit_ph-websites'))) {
		// phpcs:ignore
		show_admin_bar(false);
	}
}

add_action('after_setup_theme', 'ph_hide_client_admin_bar');
