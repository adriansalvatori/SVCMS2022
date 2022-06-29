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
function ph_setup_post_types()
{

	do_action('ph_before_register_post_type');

	/** Project Post Type */
	$project_labels = array(
		'name'               => _x('Mockups', 'post type general name', 'project-huddle'),
		'singular_name'      => _x('Mockup', 'post type singular name', 'project-huddle'),
		'add_new'            => __('New Mockup', 'project-huddle'),
		'add_new_item'       => __('Add New Mockup', 'project-huddle'),
		'edit_item'          => __('Edit Mockup Project', 'project-huddle'),
		'new_item'           => __('New Mockup Prject', 'project-huddle'),
		'all_items'          => __('Mockups', 'project-huddle'),
		'view_item'          => __('View Mockup Project', 'project-huddle'),
		'search_items'       => __('Search Mockups', 'project-huddle'),
		'not_found'          => __('No Mockups found', 'project-huddle'),
		'not_found_in_trash' => __('No Mockups found in Trash', 'project-huddle'),
		'parent_item_colon'  => '',
		'menu_name'          => __('ProjectHuddle', 'project-huddle'),
	);

	$project_args = array(
		'labels'                => apply_filters('ph_project_labels', $project_labels),
		'slug'         		    => 'mockup',
		'simple_slug'           => 'project',
		'model'					=> '\PH\Models\Mockup',
		'public'                => true,
		'menu_icon'             => 'dashicons-testimonial',
		'publicly_queryable'    => true,
		'exclude_from_search'   => true,
		'show_ui'               => true,
		'show_in_menu'          => false,
		'query_var'             => true,
		'rewrite'               => array(
			'slug'       => 'mockup',
			'with_front' => false, // permalink structure shouldn't prepended with the front base.
		),
		'show_in_rest'          => true,
		'rest_base'             => 'mockup',
		'rest_controller_class' => 'PH_REST_Posts_Controller',
		'capability_type'       => array('ph-project', 'ph-projects'),
		'map_meta_cap'          => true,
		'hierarchical'          => false,
		'supports'              => apply_filters('ph_project_supports', array('title', 'editor', 'author', 'project_image', 'collection_order', 'members', 'approvals')),
	);

	if (defined('PROJECT_HUDDLE_DEBUG') && PROJECT_HUDDLE_DEBUG) {
		$project_args['supports'][] = 'custom-fields';
	}

	register_post_type('ph-project', apply_filters('ph_register_post_type_project', $project_args));

	/**
	 * Register project image
	 *
	 * Allows us to use a post relationship to store comment posts, thumbnail, versions, etc.
	 *
	 * @since 1.0
	 */
	register_post_type(
		'project_image',
		apply_filters(
			'ph_register_post_type_project_image',
			array(
				'label'                 => __('Project Images', 'project-huddle'),
				'slug'         		    => 'image',
				'simple_slug'           => 'item',
				'model'					=> '\PH\Models\Image',
				'public'                => true,
				'exclude_from_search'   => true,
				'show_in_rest'          => true,
				'rest_base'             => 'mockup-image',
				'rewrite'               => array(
					'slug'       => '%mockup_slug%',
					'with_front' => false, // permalink structure shouldn't prepended with the front base
				),
				'rest_controller_class' => 'PH_REST_Posts_Controller',
				'show_in_menu'          => (defined('PROJECT_HUDDLE_DEBUG') && PROJECT_HUDDLE_DEBUG) ? true : false,
				'hierarchical'          => false, // add hierarchy for versions
				'map_meta_cap'          => true,
				'needs_parent'          => true,
				'capability_type'       => array('project_image', 'project_images'),
				'supports'              => apply_filters(
					'project_image_supports',
					array(
						'title',
						'excerpt',
						'thumbnail',
						'custom-fields',
						'page-attributes',
						'approvals',
						'versions',
						'parent',
						'ph_comment_location',
					)
				),
			)
		)
	);

	/**
	 * Register project image
	 *
	 * Allows us to use a post relationship to store comment posts, thumbnail, versions, etc.
	 *
	 * @since 1.0
	 */
	register_post_type(
		'ph_version',
		apply_filters(
			'ph_register_post_type_ph_version',
			array(
				'labels'              => array(
					'name'          => __('Versions'),
					'singular_name' => __('Version'),
				),
				'public'              => true,
				'exclude_from_search' => true,
				'capability_type'     => array('ph_version', 'ph_versions'),
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'show_in_menu'        => false,
				'needs_parent'        => true,
				'rewrite'             => false,
				'query_var'           => false,
				'can_export'          => false,
				'delete_with_user'    => true,
				'slug'         		  => 'image_version',
				'simple_slug'         => 'version',
				'model'				  => '\PH\Models\Version',
				'supports'            => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'comments', 'revisions', 'post-formats', 'parent'),
			)
		)
	);

	/**
	 * Register comment location post type
	 *
	 * Allows us to use a post relationship to store comment location meta data and have unique ids.
	 *
	 * @since 1.0
	 */
	register_post_type(
		'ph_comment_location',
		apply_filters(
			'ph_register_post_type_comment_location',
			array(
				'label'                 => __('Mockup Conversation Threads', 'project-huddle'),
				'public'                => true,
				'exclude_from_search'   => true,
				'show_in_menu'          => (defined('PROJECT_HUDDLE_DEBUG') && PROJECT_HUDDLE_DEBUG) ? true : false,
				'hierarchical'          => false, // non-hierarchical
				'taxonomies'            => array('ph_status'),
				'show_in_rest'          => true,
				'rest_base'             => 'mockup-thread',
				'slug'         		    => 'mockup-thread',
				'simple_slug'           => 'thread',
				'model'					=> '\PH\Models\MockupThread',
				'needs_parent'          => true,
				'rewrite'               => array(
					'slug'       => 'mockup-comment/%thread_id%',
					'with_front' => false, // permalink structure shouldn't prepended with the front base
				),
				'rest_controller_class' => 'PH_REST_Posts_Controller',
				'capability_type'       => array('ph_comment_location', 'ph_comment_locations', 'members'),
				'map_meta_cap'          => true,
				'supports'              => array(
					'comments',
					'editor',
					'custom-fields',
					'author',
					'title',
					'parent',
					'assignments',
					'resolves',
					'statuses',
				),
			)
		)
	);

	do_action('ph_after_register_post_type');
}
add_action('init', 'ph_setup_post_types');

function ph_new_mockup_admin_bar()
{
	global $wp_admin_bar;
	$wp_admin_bar->add_node(
		array(
			'id'     => 'ph_mockup', // id of the existing child node (New > Post)
			'title'  => __('Mockup', 'project-huddle'), // alter the title of existing node
			'parent' => 'new-content', // set parent to false to make it a top level (parent) node
			'href'   => admin_url('post-new.php?post_type=ph-project'),
		)
	);
}
add_action('wp_before_admin_bar_render', 'ph_new_mockup_admin_bar');

/**
 * Setup plugin taxonomies
 */
function ph_setup_taxonomies()
{
	/**
	 * Creates the "Status" taxonomy.
	 *
	 * @since 1.0
	 */
	$labels = array(
		'name'              => _x('Available Statuses', 'taxonomy general name', 'framework'),
		'singular_name'     => _x('Status', 'taxonomy singular name', 'framework'),
		'search_items'      => __('Search Available Statuses', 'framework'),
		'all_items'         => __('All Statuses', 'framework'),
		'parent_item'       => __('Parent Status', 'framework'),
		'parent_item_colon' => __('Parent Status:', 'framework'),
		'edit_item'         => __('Edit Status', 'framework'),
		'update_item'       => __('Update Status', 'framework'),
		'add_new_item'      => __('Add New Status', 'framework'),
		'new_item_name'     => __('New Status Name', 'framework'),
		'menu_name'         => __('Statuses', 'framework'),
	);

	register_taxonomy(
		'ph_status',
		array('phw_comment_loc', 'ph_comment_location', 'ph-website'),
		array(
			'hierarchical' => false,
			'labels'       => $labels,
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => array(
				'slug'       => 'status',
				'with_front' => false, // permalink structure shouldn't prepended with the front base
			), // This is the url slug
		)
	);

	/**
	 * Creates the "Status" taxonomy.
	 *
	 * @since 1.0
	 */
	$labels = array(
		'name'              => _x('Approval Statuses', 'taxonomy general name', 'framework'),
		'singular_name'     => _x('Approval Status', 'taxonomy singular name', 'framework'),
		'search_items'      => __('Search Available Approval Statuses', 'framework'),
		'all_items'         => __('All Approval Statuses', 'framework'),
		'parent_item'       => __('Parent Approval Status', 'framework'),
		'parent_item_colon' => __('Parent Approval Status:', 'framework'),
		'edit_item'         => __('Edit Approval Status', 'framework'),
		'update_item'       => __('Update Approval Status', 'framework'),
		'add_new_item'      => __('Add New Approval Status', 'framework'),
		'new_item_name'     => __('New Approval Status Name', 'framework'),
		'menu_name'         => __('Approval Statuses', 'framework'),
	);

	register_taxonomy(
		'ph_approval',
		array('project_image', 'ph-webpage'),
		array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => false,
			'show_admin_column' => false,
			'meta_box_cb'       => 'ph_approval_meta_box_select',
			'query_var'         => true,
			'rewrite'           => array(
				'slug'       => 'approval',
				'with_front' => false, // permalink structure shouldn't prepended with the front base
			), // This is the url slug
		)
	);

	/**
	 * Creates the "Status" taxonomy.
	 *
	 * @since 1.0
	 */
	$labels = array(
		'name'              => _x('Clients', 'taxonomy general name', 'framework'),
		'singular_name'     => _x('Client', 'taxonomy singular name', 'framework'),
		'search_items'      => __('Search Available Clients', 'framework'),
		'all_items'         => __('All Clients', 'framework'),
		'parent_item'       => __('Parent Client', 'framework'),
		'parent_item_colon' => __('Parent Client:', 'framework'),
		'edit_item'         => __('Edit Client', 'framework'),
		'update_item'       => __('Update Client', 'framework'),
		'add_new_item'      => __('Add New Client', 'framework'),
		'new_item_name'     => __('New Client Name', 'framework'),
		'menu_name'         => __('Clients', 'framework'),
	);

	register_taxonomy(
		'ph_client',
		array('ph-project', 'ph-website'),
		array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => false,
			'show_admin_column' => false,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'       => 'client',
				'with_front' => false, // permalink structure shouldn't prepended with the front base
			), // This is the url slug
		)
	);
}
add_action('init', 'ph_setup_taxonomies');


/**
 * Turn off comment moderation for our post type
 *
 * @param $value boolean Comment moderation on/off
 *
 * @return int Comment moderation value
 * @since 1.0
 */
function ph_disable_comment_moderation($value)
{
	if (get_post_type() == 'ph_comment_location' || get_post_type() == 'phw_comment_loc') {
		return 0;
	}

	return $value;
}
add_filter('option_comment_moderation', 'ph_disable_comment_moderation');

/**
 * Remove comments meta box from ph_comment_location post
 */
function ph_remove_meta_boxes()
{
	remove_meta_box('commentsdiv', 'ph_comment_location', 'normal');
	remove_meta_box('commentstatusdiv', 'ph_comment_location', 'normal');
}
add_action('admin_menu', 'ph_remove_meta_boxes');

if (!function_exists('ph_get_post_types')) :
	/**
	 * Get post types for plugin
	 */
	function ph_get_post_types()
	{
		return apply_filters('ph_post_types', array('ph-project', 'ph_version', 'ph-website'));
	}
endif;

if (!function_exists('ph_get_project_post_types')) {
	function ph_get_project_post_types()
	{
		return apply_filters('ph_project_post_types', array('ph-project', 'ph-website'));
	}
}

if (!function_exists('ph_get_item_post_types')) :
	function ph_get_item_post_types()
	{
		return apply_filters('ph_item_post_types', array('project_image', 'ph-webpage'));
	}
endif;

if (!function_exists('ph_get_thread_post_types')) :
	function ph_get_thread_post_types()
	{
		return apply_filters('ph_item_post_types', array('ph_comment_location', 'phw_comment_loc'));
	}
endif;

if (!function_exists('ph_get_child_post_types')) :
	/**
	 * Get post types for plugin
	 */
	function ph_get_child_post_types()
	{
		return apply_filters('ph_child_post_types', array('ph_comment_location', 'project_image', 'ph-webpage', 'phw_comment_loc'));
	}
endif;

if (!function_exists('ph_get_all_post_types')) :
	/**
	 * Get post types for plugin
	 */
	function ph_get_all_post_types()
	{
		return apply_filters(
			'ph_all_post_types',
			array_unique(
				array_merge(
					ph_get_post_types(),
					ph_get_child_post_types()
				)
			)
		);
	}
endif;

/**
 * Custom project_image link
 *
 * @param $link
 * @param $post
 *
 * @return string
 */
function ph_image_post_type_link($link, $post)
{
	if ($post->post_type !== 'project_image') {
		return $link;
	}

	$project_id = get_post_meta($post->ID, 'parent_id', true);

	if ($project_id && $project = get_post($project_id)) {
		$object = get_post_type_object($project->post_type);

		return untrailingslashit(str_replace('%mockup_slug%/', trailingslashit($object->rewrite['slug']) . $project->post_name . '/#', $link));
	}

	return untrailingslashit(str_replace('%mockup_slug%', '', $link));
}

add_filter('post_type_link', 'ph_image_post_type_link', 10, 2);

/**
 * Define default workflow status on new posts
 */
function ph_set_default_object_status($post_id, $post)
{
	if ('publish' === $post->post_status) {
		// post type must support approvals
		if (!post_type_supports($post->post_type, 'statuses')) {
			return;
		}

		// set defaults
		$defaults = array(
			'ph_status' => ph_get_default_workflow_status($post->ID),
		);

		$taxonomies = get_object_taxonomies($post->post_type);
		foreach ((array) $taxonomies as $taxonomy) {

			$terms = wp_get_post_terms($post_id, $taxonomy);

			// if no terms set, set the default status
			if (empty($terms) && array_key_exists($taxonomy, $defaults)) {
				wp_set_object_terms($post_id, $defaults[$taxonomy], $taxonomy);
			}
		}
	}
}
add_action('save_post', 'ph_set_default_object_status', 100, 2);
