<?php

/**
 * Post Type Functions
 *
 * @package     ProjectHuddle
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}


/**
 * Registers and sets up the Downloads custom post type
 *
 * @since 1.0
 * @return void
 */
function ph_setup_website_post_types()
{

	do_action('ph_website_before_register_post_type');

	/** Project Post Type */
	$website_labels = array(
		'name'               => _x('Websites', 'post type general name', 'project-huddle'),
		'singular_name'      => _x('Website', 'post type singular name', 'project-huddle'),
		'add_new'            => __('Add Website', 'project-huddle'),
		'add_new_item'       => __('Add New Website', 'project-huddle'),
		'edit_item'          => __('Edit Website', 'project-huddle'),
		'new_item'           => __('New Website', 'project-huddle'),
		'all_items'          => __('Websites', 'project-huddle'),
		'view_item'          => __('View Website', 'project-huddle'),
		'search_items'       => __('Search Websites', 'project-huddle'),
		'not_found'          => __('No Websites found', 'project-huddle'),
		'not_found_in_trash' => __('No Websites found in Trash', 'project-huddle'),
		'parent_item_colon'  => '',
		'menu_name'          => __('Websites', 'project-huddle'),
	);

	$website_args = array(
		'labels'                => apply_filters('ph_website_labels', $website_labels),
		'public'                => true,
		'menu_icon'             => 'dashicons-welcome-widgets-menus',
		'publicly_queryable'    => true,
		'exclude_from_search'   => true,
		'show_ui'               => true,
		'show_in_menu'          => false,
		'query_var'             => true,
		'rewrite'               => array(
			'slug'       => 'website',
			'with_front' => false, // permalink structure shouldn't prepended with the front base
		),
		'capability_type'       => array('ph-website', 'ph-websites'),
		'map_meta_cap'          => true,
		'hierarchical'          => false,
		'show_in_rest'          => true,
		'rest_base'             => 'website',
		'slug'         		    => 'website',
		'simple_slug'           => 'project',
		'model'					=> '\PH\Models\Website',
		'rest_controller_class' => 'PH_REST_Posts_Controller',
		'taxonomies'            => array('ph_status', 'ph_approval'),
		'supports'              => apply_filters(
			'ph_website_supports',
			array(
				'approvals',
				'title',
				'author',
				'custom-fields',
				'members',
			)
		),
	);

	if (defined('PROJECT_HUDDLE_DEBUG') && PROJECT_HUDDLE_DEBUG) {
		$project_args['supports'][] = 'custom-fields';
	}

	register_post_type('ph-website', apply_filters('ph_register_post_type_ph-website', $website_args));

	/**
	 * Register comment location post type
	 *
	 * Allows us to use a post relationship to store comment location meta data and have unique ids.
	 * @since 1.0
	 */
	register_post_type(
		'ph-webpage',
		apply_filters(
			'ph_register_post_type_ph-webpage',
			array(
				'labels' => [
					'name'               => _x('Pages', 'post type general name', 'project-huddle'),
					'singular_name'      => _x('Page', 'post type singular name', 'project-huddle'),
					'add_new'            => __('Add Page', 'project-huddle'),
					'add_new_item'       => __('Add New Page', 'project-huddle'),
					'edit_item'          => __('Edit Page', 'project-huddle'),
					'new_item'           => __('New Page', 'project-huddle'),
					'all_items'          => __('Pages', 'project-huddle'),
					'view_item'          => __('View Page', 'project-huddle'),
					'search_items'       => __('Search Pages', 'project-huddle'),
					'not_found'          => __('No Pages found', 'project-huddle'),
					'not_found_in_trash' => __('No Pages found in Trash', 'project-huddle'),
					'parent_item_colon'  => '',
					'menu_name'          => __('Pages', 'project-huddle'),
				],
				'label'                 => __('Web Pages', 'project-huddle'),
				'public'                => true,
				'exclude_from_search'   => true,
				'show_in_menu'          => (defined('PROJECT_HUDDLE_DEBUG') && PROJECT_HUDDLE_DEBUG) ? true : false,
				'hierarchical'          => false, // non-hierarchical
				'show_in_rest'          => true,
				'capability_type'       => array('ph-webpage', 'ph-webpages'),
				'map_meta_cap'          => true,
				'needs_parent'          => true,
				'rewrite'               => array(
					'slug'       => 'website-page',
					'with_front' => false, // permalink structure shouldn't prepended with the front base
				),
				'rest_base'             => 'website-page',
				'slug'         		    => 'page',
				'simple_slug'           => 'item',
				'model'					=> '\PH\Models\Page',
				'rest_controller_class' => 'PH_REST_Posts_Controller',
				'taxonomies'            => array('ph_status', 'ph_approval'),
				'supports'              => array(
					'custom-fields',
					'title',
					'approvals',
					'parent',
					'page-attributes',
					'phw_comment_loc',
				),
			)
		)
	);

	do_action('ph_website_after_register_post_type');

	/**
	 * Register comment location post type
	 *
	 * Allows us to use a post relationship to store comment location meta data and have unique ids.
	 * @since 1.0
	 */
	register_post_type(
		'phw_comment_loc',
		apply_filters(
			'ph_register_post_type_comment_location_website',
			array(
				'label'                 => __('Website Conversation Threads', 'project-huddle'),
				'public'                => true,
				'exclude_from_search'   => true,
				'rewrite'               => array(
					'slug'       => 'website-thread/%thread_id%',
					'with_front' => false, // permalink structure shouldn't prepended with the front base
				),
				'show_in_menu'          => (defined('PROJECT_HUDDLE_DEBUG') && PROJECT_HUDDLE_DEBUG) ? true : false,
				'hierarchical'          => false, // non-hierarchical
				'taxonomies'            => array('ph_status'),
				'capability_type'       => array('phw_comment_loc', 'phw_comment_locs'),
				'map_meta_cap'          => true,
				'show_in_rest'          => true,
				'needs_parent'          => true,
				'slug'         		    => 'website-thread',
				'simple_slug'           => 'thread',
				'model'					=> '\PH\Models\WebsiteThread',
				'rest_base'             => 'website-thread',
				'rest_controller_class' => 'PH_REST_Posts_Controller',
				'supports'              => array(
					'comments',
					'editor',
					'custom-fields',
					'author',
					'title',
					'resolves',
					'page-attributes',
					'assignments',
					'members',
				),
			)
		)
	);

	do_action('ph_website_after_register_post_type');
}

add_action('init', 'ph_setup_website_post_types');

function ph_new_website_admin_bar()
{
	global $wp_admin_bar;
	$wp_admin_bar->add_node(
		array(
			'id'     => 'ph_website', // id of the existing child node (New > Post)
			'title'  => __('Website', 'project-huddle'), // alter the title of existing node
			'parent' => 'new-content', // set parent to false to make it a top level (parent) node
			'href'   => admin_url('post-new.php?post_type=ph-website'),
		)
	);
}
add_action('wp_before_admin_bar_render', 'ph_new_website_admin_bar');

/**
 * Setup installed site on ProjectHuddle
 */
function ph_setup_first_site()
{
	$this_site_page = get_option('ph_site_post');
	if (!$this_site_page) {
		// Insert the page into the database
		$page_id = wp_insert_post(
			array(
				'post_title'  => get_bloginfo('title'),
				'post_status' => 'publish',
				'post_type'   => 'ph-website',
			)
		);
	} else {
		$page_id = $this_site_page;
	}

	// add meta
	update_post_meta($page_id, 'ph_website_url', get_site_url());
	update_post_meta($page_id, 'ph_installed', true);

	// maybe regenerate key
	ph_generate_api_key($page_id, false, get_site_url());

	// update post id
	update_option('ph_site_post', $page_id);
}

/**
 * Disable all websites post lock
 */
function ph_disable_post_lock()
{
	if ('ph-website' === get_current_screen()->post_type) {
		add_filter('wp_check_post_lock_window', '__return_zero');
	}
}

add_action('load-edit.php', 'ph_disable_post_lock');

/**
 * Disable post lock dialog
 *
 * @param $bool
 * @param $post
 * @param $user
 *
 * @return bool
 */
function ph_disable_post_lock_dialog($bool, $post, $user)
{
	if ('ph-website' === $post->post_type) {
		return false;
	}

	return $bool;
}

add_filter('show_post_locked_dialog', 'ph_disable_post_lock_dialog', 10, 3);

/**
 * Register our parent post type with ProjectHuddle
 * Used for permissions, etc.
 *
 * @param $types
 *
 * @return array
 */
function ph_add_website_post_types($types)
{
	$types[] = 'ph-website';

	return $types;
}

add_filter('ph_post_types', 'ph_add_website_post_types');

/**
 * Register our child post types with ProjectHuddle
 * Used for permissions, etc.
 *
 * @param $types
 *
 * @return array
 */
function ph_add_website_child_post_types($types)
{
	$types[] = 'ph-webpage';
	$types[] = 'phw_comment_loc';

	return $types;
}

add_filter('ph_child_post_types', 'ph_add_website_child_post_types');

function ph_disable_gutenberg($current_status, $post_type)
{
	// Use your post type key instead of 'product'
	if (in_array($post_type, ph_get_post_types()) || in_array($post_type, ph_get_child_post_types())) {
		return false;
	}
	return $current_status;
}
add_filter('use_block_editor_for_post_type', 'ph_disable_gutenberg', 10, 2);

// WP automatically adds "Auto Draft" as post title. Remove it manually.
add_filter( 'wp_insert_post_data' , 'modify_post_title' , '99', 1 );

function modify_post_title( $data ) {
 	if( 'ph-website' === $data['post_type'] && 'Auto Draft' === $data['post_title'] ) {
		$data['post_title'] =  ''; //Updates the post title to your new title.
	}
  	return $data; // Returns the modified data.
}
