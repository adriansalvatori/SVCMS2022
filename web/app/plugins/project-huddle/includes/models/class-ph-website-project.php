<?php

/**
 * Website Project Data
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       2.6.0
 */

use PH\Models\Website;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * PH_Project Class
 *
 * This class handles the project data
 *
 * @since 2.6.0
 */
class PH_Website_Project extends PH_Project
{
	protected $rest_base = 'website';

	/**
	 * Model to use for getting model data
	 *
	 * @var string
	 */
	protected $model = '\PH\Models\Website';

	/**
	 * Post type
	 *
	 * @var string
	 */
	public $post_type = 'ph-website';

	/**
	 * Slug for actions
	 *
	 * @var string
	 */
	public $action_slug = 'project';

	/**
	 * For website commenting
	 *
	 * @var string
	 */
	public $endpoint_type = 'website';

	/**
	 * Register actions
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		parent::__construct();

		// check for trailing slash in website_url query.
		add_filter('ph_website_url_collection_query_args', array($this, 'smart_match_url'), 10, 3);

		// comment scrolling
		add_action('rest_api_init', array($this, 'comment_scroll'));
		add_action('rest_api_init', array($this, 'approved_field'));
		add_action('ph_website_script_loaded', array($this, 'clear_scroll'));

		add_action("updated_postmeta", [$this, 'maybe_update_pages'], 10, 4);
	}

	/**
	 * Updates urls in page posts when updating the website url
	 *
	 * @param string $value
	 * @param integer $post_id
	 * @param string $meta_key
	 * 
	 * @return void
	 */
	public function maybe_update_pages($meta_id, $post_id, $meta_key, $value)
	{
		// only for our post type
		if (get_post_type($post_id) !== $this->post_type) {
			return;
		}
		// only if the website url is being updated
		if ($meta_key !== 'website_url') {
			return;
		}

		// get all page ids of website
		$pages = ph_get_website_pages([
			'id' => $post_id,
			'fields' => 'ids',
			'posts_per_page' => -1
		]);
		if (empty($pages)) {
			return;
		}

		// update urls in pages
		foreach ($pages as $id) {
			// get pages meta
			$page_website_url = get_post_meta($id, 'website_url', true);
			$page_url = get_post_meta($id, 'page_url', true);

			// replace new website url in string
			$data = str_replace($page_website_url, $value, $page_url);

			// update meta
			update_post_meta($id, 'page_url', $data);
			update_post_meta($id, 'website_url', $value);
		}
	}

	/**
	 * Get post type route
	 *
	 * @return string
	 */
	public function route()
	{
		return $this->route_base . '/' . $this->endpoint_type;
	}

	/**
	 * Schema for post meta
	 *
	 * @return array
	 */
	public function schema()
	{
		return apply_filters(
			'ph_website_meta',
			array(
				'website_url'            => array(
					'description' => esc_html__('URL for the website.', 'project-huddle'),
					'type'        => 'string',
					'default'     => '',
					'required'    => true,
				),
				'pages'                  => array(
					'description' => esc_html__('An array of page objects belonging to project.', 'project-huddle'),
					'type'        => 'array',
					'default'     => array(),
					'items'       => array(
						'description' => esc_html__('Page object.', 'project-huddle'),
						'type'        => 'object',
						'readonly'    => true,
					),
					'readonly'    => true,
				),
				'allow_guests'  => array(
					'description' => esc_html__('Allow anyone to access the project and leave comments.', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => false,
				),
				'force_login'  => array(
					'description' => esc_html__('Force users to login instead of using an access link.', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => false,
				),
				'thread_subscribers'     => array(
					'description' => esc_html__('Option for new thread subscribers', 'project-huddle'),
					'type'        => 'string',
					'default'     => 'all',
				),
				'project_approval'   => array(
					'description' => esc_html__('Approvals allowed?', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => apply_filters('default_website_approval_setting', false),
				),
				'project_unapproval' => array(
					'description' => esc_html__('Unapprovals allowed?', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => apply_filters('default_website_unapproval_setting', true),
				),
				'webhook' => array(
					'description' => esc_html__('Enter the webhook URL here', 'project-huddle'),
					'type'        => 'string',
					'default'     => ''
				),
				'ph_installed'           => array(
					'description' => esc_html__('Has the connected site been verified as installed?', 'project-huddle'),
					'type'        => 'boolean',
					'default'     => false,
				),
				'child_site'             => array(
					'description' => esc_html__('Connected child site', 'project-huddle'),
					'type'        => 'string',
					'default'     => '',
				),
				'child_plugin_installed' => array(
					'description' => esc_html__('Connected child site plugin installed', 'project-huddle'),
					'type'        => 'string',
					'default'     => '',
				),
			)
		);
	}

	/**
	 * Collection query params
	 *
	 * Allows querying collections via the following meta data.
	 *
	 * @return array
	 */
	public function collection_params()
	{
		return apply_filters(
			'ph_website_collection_parameters',
			array(
				'website_url'    => array(
					'description' => esc_html__('URL for the website.', 'project-huddle'),
					'type'        => 'string',
					'meta'        => true,
				),
				'project_member' => array(
					'description' => esc_html__('Limit results to only those you are a member of.', 'project-huddle'),
					'type'        => 'boolean',
					'meta'        => false,
				),
			)
		);
	}

	public function resolve_status()
	{
		register_rest_field(
			$this->post_type,
			'resolve_status',
			array(
				'update_callback' => null,
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					return (array) ph_get_website_resolve_status($post['id']);
				},
				'schema'          => array(
					'description' => esc_html__('Array of comment data for the image.', 'project-huddle'),
					'type'        => 'array',
					// 'readonly'    => true,
					'items'       => array(
						'description' => esc_html__('Associated number.', 'project-huddle'),
						'type'        => 'integer',
					),
				),
			)
		);
	}

	/**
	 * This needs to be a bit more dynamic in case the project url changes
	 *
	 * @return void
	 */
	public function approved_field()
	{
		register_rest_field(
			$this->post_type,
			'approved',
			array(
				'update_callback' => function ($value, $post, $attr, $request, $object_type) {
					// add permissions filter for granular control.
					if (!apply_filters("ph_website_update_{$this->action_slug}_{$attr}_allowed", true, $post, $value)) {
						return new WP_Error('rest_forbidden_context', __('Sorry, you are not allowed to do this.', 'project-huddle'), array('status' => rest_authorization_required_code()));
					}

					$site = new Website($post->ID);
					$site->saveApprovalStatus($value);

					// run action on update.
					do_action("ph_website_rest_update_{$this->action_slug}_attribute", $attr, $value, $post);

					// Schema handles sanitization.
					return $value;
				},
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					return (bool) (new Website($post['id']))->isApproved();
				},
				'schema'          => array(
					'description' => esc_html__('Is this approved?', 'project-huddle'),
					'type'        => 'boolean',
				),
			)
		);
	}

	/**
	 * Also check for trailing slash in website_url attribute query
	 * instead of exact query math
	 *
	 * @param array           $args Arguments.
	 * @param string          $name Parameter name.
	 * @param WP_REST_Request $request Request.
	 *
	 * @return array
	 */
	public function smart_match_url($args, $name, $request)
	{
		if (!$request[$name]) {
			return $args;
		}
		// get https and http variants.
		$url   = $request[$name];
		$parse = parse_url($url);

		if ('http' === $parse['scheme']) {
			$url_https = preg_replace('/^http:/i', 'https:', $url);
		} else {
			$url_https = $url;
			$url       = preg_replace('/^https:/i', 'http:', $url);
		}

		return array(
			'relation' => 'OR',
			array(
				'key'     => 'ph_' . $name,
				'value'   => array(
					untrailingslashit($url),
					trailingslashit($url),
					untrailingslashit($url_https),
					trailingslashit($url_https),
				),
				'compare' => 'IN',
			),
			array(
				'key'     => $name,
				'value'   => array(
					untrailingslashit($url),
					trailingslashit($url),
					untrailingslashit($url_https),
					trailingslashit($url_https),
				),
				'compare' => 'IN',
			),
		);
	}

	/**
	 * Gets options used for project
	 *
	 * @since 1.0
	 *
	 * @param int $post_id ID of project.
	 *
	 * @return array
	 */
	public function get_project_options($post_id = 0)
	{
		global $post;
		$post_id = $post_id ? $post_id : $post->ID;

		$options = array(
			// global options.
			'global'  => (array) $this->get_global_project_options(),
			// project options.
			'project' => array(
				'project_access' => get_post_meta($post_id, 'ph_project_access', true),
				'query_strings'  => apply_filters('ph_website_ignore_query_strings', false, $post_id),
				'retina_images'  => get_post_meta($post_id, 'ph_retina', true),
				'sharing'        => get_post_meta($post_id, 'ph_project_sharing', true),
				'tooltip'        => apply_filters('ph_comment_tooltip', true, get_post($post_id)),
			),
		);

		return $options;
	}

	/**
	 * Get global project options.
	 *
	 * @return array
	 */
	public function get_global_project_options()
	{
		$options = array(
			'avatar_default'            => get_option('avatar_default'),
			'auto_close'                => get_option('ph_auto_close', 'on') == 'on' ? true : false,
			'ph_image_background_color' => get_option('ph_image_bg') ? get_option('ph_image_bg') : false,
		);

		return apply_filters('ph_global_website_options', $options);
	}

	/**
	 * Comment Scroll Field
	 */
	public function comment_scroll()
	{
		register_rest_field(
			$this->post_type,
			'comment_scroll',
			array(
				'get_callback'   => function () {
					return PH()->session->get('ph_comment_id');
				},
				'comment_scroll' => array(
					'description' => esc_html__('Comment scroll information', 'project-huddle'),
					'type'        => 'string',
					'readonly'    => true,
				),
			)
		);
	}

	/**
	 * Clear Comment Scroll
	 */
	public function clear_scroll()
	{
		PH()->session->clear('ph_comment_id');
	}

	/**
	 * Gets all commented pages from a specific site id
	 *
	 * @param bool $site_id ID of site.
	 *
	 * @return array Pages and comments
	 */
	public function get_pages($site_id = false)
	{
		global $post;

		$site_id = $site_id ? $site_id : 0;

		if (is_object($post)) {
			$site_id = $post->ID;
		}

		if (!$site_id) {
			return;
		}

		$pages = get_post_meta($site_id, 'ph_webpages', true);

		$page_data = array();
		if (isset($pages) && !empty($pages)) {
			foreach ($pages as $page_id => $page_url) {
				// validate existing page id
				if (!$page_id || get_post_status($page_id) != 'publish') {
					continue;
				}
				if ($data = PH()->page->data($site_id, $page_id, $page_url)) {
					$page_data[$page_id] = $data;
				}
			}
		}

		return array_values($page_data);
	}

	/**
	 * Add page to website meta
	 */
	public function add_page($args)
	{
		// Parse incoming $args into an array and merge it with $defaults
		$args = wp_parse_args(
			$args,
			array(
				'website_id' => 0,
				'webpage_id' => 0,
				'location'   => 0,
			)
		);

		if (!$args['webpage_id'] || !$args['location']) {
			return false;
		}

		// get current pages
		$pages = (array) get_post_meta($args['website_id'], 'ph_webpages', true);

		// if it already exists and not published, remove
		if ($existing_id = array_search(esc_html($args['location']), $pages)) {
			// if not published, remove
			if (get_post_status($existing_id) != 'publish') {
				unset($pages[$existing_id]);
			}
		}

		// add our page in form of i.e. 89 => '/contact'
		$pages[(int) $args['webpage_id']] = esc_html($args['location']);

		// update meta
		update_post_meta($args['website_id'], 'ph_webpages', $pages);

		return true;
	}

	/**
	 * Updates page order in database
	 *
	 * @param int   $id
	 * @param array $page_ids
	 *
	 * @return bool Success/Failure
	 */
	public function set_page_order($id = 0, $page_ids = array())
	{
		// get current pages
		$pages = (array) get_post_meta($id, 'ph_webpages', true);

		// return if empty
		if (empty($pages)) {
			return false;
		}

		// order by ids
		$pages_ordered = array_replace(array_flip(array_map('intval', $page_ids)), $pages);

		// update order
		update_post_meta($id, 'ph_webpages', $pages_ordered);

		return true;
	}

	public function get_users($post_id = 0)
	{
		global $post;

		if (!$post_id) {
			$post_id = $post->ID;
		}

		if (is_object($post_id) && isset($post_id->ID)) {
			$post_id = $post_id->ID;
		}

		$users = ph_get_project_members($post_id);

		return $users;
	}

	public function get_assign_filters()
	{
		return apply_filters(
			'ph_assign_filters',
			array(
				array(
					'name' => __('All Comments', 'project-huddle'),
					'slug' => 'all',
				),
				array(
					'name' => __('Assigned To Me', 'project-huddle'),
					'slug' => 'mine',
				),
				array(
					'name' => __('Unassigned', 'project-huddle'),
					'slug' => 'unassigned',
				),
			),
			get_the_ID()
		);
	}
}
