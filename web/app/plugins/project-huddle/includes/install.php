<?php

/**
 * Functions run on installation
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Sets up custom post types, taxonomies, flushes rewrite rules
 */
function ph_install()
{
	// allow filtering of activation
	do_action('ph_activate');

	do_action('wp_session_init');
	ph_update_members();
	ph_thread_members_create_table();

	// setup custom post types
	ph_setup_post_types();
	ph_setup_taxonomies();

	// website post type
	include_once PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-post-types.php';
	include_once PH_WEBSITE_PLUGIN_DIR . 'includes/ph-website-functions.php';
	ph_setup_website_post_types();
	ph_setup_first_site();

	$roles = new PH_Roles;
	$roles->add_roles();
	$roles->remove_caps();
	$roles->add_caps();

	// add first status
	wp_insert_term('Backlog', 'ph_status');
	wp_insert_term('To Do', 'ph_status');
	wp_insert_term('Doing', 'ph_status');
	wp_insert_term('Done', 'ph_status');

	wp_insert_term(__('Approved', 'project-huddle'), 'ph_approval', array('slug' => 'approved'));
	wp_insert_term(__('Unapproved', 'project-huddle'), 'ph_approval', array('slug' => 'unapproved'));

	// flush rewrite rules.
	flush_rewrite_rules();
	set_transient('_ph_installed', true, 30);

	// Bail if activating from network, or bulk
	if (is_network_admin() || isset($_GET['activate-multi'])) {
		return;
	}

	// Add the transient to redirect
	set_transient('_ph_activation_redirect', true, 30);

	// set current version in database if not set yet
	if (null === get_site_option('ph_db_version', null)) {
		update_site_option('ph_db_version', PH_VERSION);
	}
}
register_activation_hook(PH_PLUGIN_FILE, 'ph_install');
register_deactivation_hook(PH_PLUGIN_FILE, 'flush_rewrite_rules');


/**
 * Flush permalinks after install
 *
 * @return void
 */
function ph_after_install()
{
	$installed = get_transient('_ph_installed');
	if ($installed) {
		flush_rewrite_rules();
		delete_transient('_ph_installed');
	}
}
add_action('admin_init', 'ph_after_install');
