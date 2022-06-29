<?php

/**
 * Webpage Data
 *
 * @package     ProjectHuddle
 * @copyright   Copyright (c) 2015, Andre Gagnon
 * @since       2.6.0
 */

use PH\Models\Page;

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * PH_Project Class
 *
 * This class handles the project data
 *
 * @since 1.0
 */
class PH_Website_Page extends PH_Item
{
	protected $rest_base = 'website-page';

	/**
	 * Model to use for getting model data
	 *
	 * @var string
	 */
	protected $model = '\PH\Models\Page';

	/**
	 * Post type
	 *
	 * @var string
	 */
	public $post_type = 'ph-webpage';

	/**
	 * Parent collection post type
	 *
	 * @var string
	 */
	public $parent_post_type = 'ph-website';

	/**
	 * Collection name
	 *
	 * @var string
	 */
	public $collection_name = 'pages';

	/**
	 * Slug for actions
	 *
	 * @var string
	 */
	public $action_slug = 'page';

	/**
	 * For website commenting
	 *
	 * @var string
	 */
	public $endpoint_type = 'website';

	/**
	 * Actions and filters
	 */
	public function __construct()
	{
		parent::__construct();

		// store original project url with page url to serve relative url.
		add_action('rest_api_init', array($this, 'page_url_field'));
		add_action('rest_api_init', array($this, 'resolve_status'));
		add_action('rest_api_init', array($this, 'approved_field'));

		// make sure page doesn't exist with same url before creating.
		add_filter("rest_pre_insert_{$this->post_type}", array($this, 'check_for_existing_page'), 10, 2);

		add_filter("rest_{$this->post_type}_collection_params", array($this, 'page_url_collection_param'), 10, 2);

		// filter page_url query argument to smart match the url.
		add_filter('ph_page_url_collection_query_args', array($this, 'smart_match_url'), 10, 3);

		// pages user order.
		add_filter("rest_prepare_{$this->parent_post_type}", array($this, 'user_order_data'), 10, 3);

		// sort by menu order
		add_filter("ph_{$this->collection_name}_collection_data", array($this, 'sort_by_menu_order'));
		add_filter("ph_{$this->collection_name}_collection_response", [$this, 'load_current_page']);

		add_action("updated_post_meta", [$this, 'maybe_clear_items_transient'], 10, 4);
	}


	public function maybe_clear_items_transient($meta_id, $object_id, $meta_key, $_meta_value)
	{
		if ($this->post_type !== get_post_type($object_id) || $meta_key !== 'approved') {
			return;
		}
		$id = get_post_meta($object_id, 'parent_id', true);
		delete_transient("ph_approved_status_" . $id);
	}

	/**
	 * Schema for post meta
	 *
	 * @return array
	 */
	public function schema()
	{
		return apply_filters(
			'ph_website_page_meta',
			array(
				'user_order' => array(
					'description' => esc_html__('The user\'s custom thread order', 'project-huddle'),
					'type'        => 'array',
					'items'       => array(
						'description' => esc_html__('Custom order ids.', 'project-huddle'),
						'type'        => 'integer',
						'readonly'    => true,
					),
					'readonly'    => true,
				),
			)
		);
	}

	/**
	 * This needs to be a bit more dynamic in case the project url changes
	 *
	 * @return void
	 */
	public function page_url_field()
	{
		register_rest_field(
			$this->post_type,
			'page_url',
			array(
				'update_callback' => function ($value, $post, $attr, $request, $object_type) {
					// add permissions filter for granular control.
					if (!apply_filters("ph_website_update_{$this->action_slug}_{$attr}_allowed", true, $post, $value)) {
						return new WP_Error('rest_forbidden_context', __('Sorry, you are not allowed to do this.', 'project-huddle'), array('status' => rest_authorization_required_code()));
					}

					// update and store meta data.
					update_post_meta($post->ID, $attr, esc_url($value));

					// get project ID.
					$project_id = get_post_meta($post->ID, 'parent_id', true);

					// store original project url in post meta in case original changes.
					update_post_meta($post->ID, 'website_url', get_post_meta($project_id, 'website_url', true));

					// run action on update.
					do_action("ph_website_rest_update_{$this->action_slug}_attribute", $attr, $value, $post);

					// Schema handles sanitization.
					return $value;
				},
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					$data = get_post_meta($post['id'], $attr, true);

					// allow filtering.
					$data = apply_filters("ph_website_rest_get_{$this->action_slug}_attribute", $data, $attr, $post);

					// Schema handles sanitization.
					return htmlspecialchars_decode($data);
				},
				'schema'          => array(
					'description' => esc_html__('Full url for the page.', 'project-huddle'),
					'type'        => 'string',
					'format'      => 'uri',
					'required'    => true,
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

					Page::get($post->ID)->saveApprovalStatus($value);

					// run action on update.
					do_action("ph_website_rest_update_{$this->action_slug}_attribute", $attr, $value, $post);

					// Schema handles sanitization.
					return $value;
				},
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					return (bool) Page::get($post['id'])->isApproved();
				},
				'schema'          => array(
					'description' => esc_html__('Is this approved?', 'project-huddle'),
					'type'        => 'boolean',
				),
			)
		);
	}

	/**
	 * Add parent model to collection parameters
	 *
	 * @param array  $query_params Query parameters.
	 * @param string $post_type    Post type.
	 *
	 * @return array
	 */
	public function page_url_collection_param($query_params, $post_type)
	{
		$query_params['page_url'] = array(
			'description' => __('Limit result set to items from a specific page url.'),
			'type'        => 'integer',
		);

		return $query_params;
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
			"ph_{$this->post_type}_collection_parameters",
			array(
				'page_url' => array(
					'description' => esc_html__('Url for the page.', 'project-huddle'),
					'type'        => 'string',
					'meta'        => true,
				),
			)
		);
	}

	/**
	 * Also check for trailing slash in website_url attribute query
	 * instead of exact query math
	 *
	 * @param array           $args Query arguments.
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

		if ($parse['scheme'] === 'http') {
			$url_https = preg_replace('/^http:/i', 'https:', $url);
		} else {
			$url_https = $url;
			$url       = preg_replace('/^https:/i', 'http:', $url);
		}

		return array(
			'key'     => $name,
			'value'   => array(
				untrailingslashit($url),
				trailingslashit($url),
				untrailingslashit($url_https),
				trailingslashit($url_https),
			),
			'compare' => 'IN',
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
					return (array) ph_get_page_resolve_status($post['id']);
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

	public function user_order_data($response, $post)
	{
		$user_id = get_current_user_id();

		// on admin only.
		if (!is_admin() || !$user_id) {
			return $response;
		}

		// get threads order stored in user meta by parent id.
		$response->data['user_order'] = get_user_meta($user_id, 'pages_order_' . (int) $response->data['id'], true);

		return $response;
	}

	/**
	 * Check for existing webpage
	 *
	 * @param WP_Post         $prepared_post Prepared post object.
	 * @param WP_Rest_Request $request  Request object.
	 *
	 * @return object Request object
	 */
	public function check_for_existing_page($prepared_post, $request)
	{
		$params = $request->get_params();
		$method = $request->get_method();

		// if we're creating a new page and have page_url.
		if ('POST' === $method && isset($params['page_url'])) {
			// get pages with same url.
			$items = $this->rest->fetch(
				array(
					'page_url'  => $params['page_url'],
					'parent_id' => $params['parent_id'],
					'status'    => 'publish',
				)
			);

			// if page already exists.
			if (!empty($items) && isset($items[0])) {
				wp_send_json($items[0], '208');
			}
		}

		// return prepared post as usual.
		return $prepared_post;
	}

	public function get_page_title($url)
	{
		if (!class_exists('WP_Http')) {
			include_once ABSPATH . WPINC . '/class-http.php';
		}
		$request = new WP_Http();
		$result  = $request->request($url);
		if (is_wp_error($result)) {
			return false;
		}

		if (preg_match('#<title>(.+)<\/title>#iU', $result['body'], $t)) {
			return trim($t[1]);
		} else {
			return false;
		}
	}

	/**
	 * Fallback filters
	 *
	 * @param $prepared_post
	 * @param $request
	 *
	 * @return object
	 */
	public function filters($prepared_post, $request)
	{
		$method = $this->map_method($request->get_method());

		return apply_filters('ph_' . $method . '_website_page', $prepared_post, $request);
	}

	/**
	 * Get page data
	 *
	 * @deprecated Use get_items method instead
	 *
	 * @param bool     $site_id
	 * @param      $page_id
	 * @param      $page_url
	 *
	 * @return array|bool
	 */
	public function data($site_id = false, $page_id = '', $page_url = '')
	{
		// bail if deleted
		if (false == get_post_status($page_id)) {
			return false;
		}

		$url           = $this->url($site_id, $page_url);
		$threads       = PH()->website_thread->get_website_threads($page_id);
		$comment_count = sizeof($threads);

		// bail if no comments
		if ($comment_count == 0) {
			return false;
		}

		$user = wp_get_current_user();

		return array(
			'title'         => esc_html(get_the_title($page_id)),
			'id'            => (int) $page_id,
			'page_url'      => $page_url,
			'url'           => $url,
			'comment_count' => $comment_count,
			'user_order'    => get_user_meta($user->ID, 'threads_order_' . $page_id, true),
			'threads'       => $threads,
		);
	}

	/**
	 * Get all comment threads on specific page
	 *
	 * @param bool   $id     Page ID
	 * @param string $status Publish status
	 *
	 * @return mixed
	 */
	public function get_comment_threads($id = false, $status = 'publish')
	{
		global $post;
		$id = $id ? $id : $post->ID;

		$comment_threads = get_post_meta($id, 'comment_locations', true);

		// check status
		foreach ($comment_threads as $key => $comment_thread) {
			if ($status != get_post_status($comment_thread) || !$comment_thread) {
				unset($comment_threads[$key]);
				continue;
			}
		}

		return $comment_threads;
	}

	/**
	 * Gets the website URL
	 *
	 * @param bool   $id   ID of website project
	 * @param string $page Page url
	 *
	 * @return mixed|string
	 */
	public function url($id = false, $page = '')
	{
		global $post;
		$id = $id ? $id : $post->ID;

		// normalize url
		$website_url = parse_url(get_post_meta((int) $id, 'ph_website_url', true));
		$port        = isset($website_url['port']) ? ':' . $website_url['port'] : '';
		$url         = $website_url['scheme'] . '://' . $website_url['host'] . $port . $page;

		return $url;
	}

	/**
	 * New webpage post
	 *
	 * @return bool|int ID or false
	 */
	public function new_page($title = 'Webpage')
	{
		// arguments to create a new post
		$post_args = apply_filters(
			'ph_new_comment_location_website_args',
			array(
				'post_title'  => esc_html($title),
				'post_type'   => 'ph-webpage',
				'post_status' => 'publish',
			)
		);

		// insert new post and store id
		$id = wp_insert_post($post_args, true);

		// return id or false
		return is_wp_error($id) ? false : $id;
	}

	/**
	 * Gets page ID from URL
	 *
	 * @param $site_id
	 * @param $url
	 *
	 * @return bool|int
	 */
	public function get_webpage_id($site_id, $url)
	{
		// get existing subdirectories
		$existing = (array) get_post_meta($site_id, 'ph_webpages', true);

		// return id of page in array
		$id = array_search($url, $existing);

		// make sure it's published
		if (get_post_status($id) != 'publish') {
			return false;
		}

		// return webpage id
		return (int) $id;
	}

	/**
	 * Adds a comment location to the website
	 *
	 * @param array $args Webpage id and thread id
	 *
	 * @return void
	 */
	public function add_thread($args = array())
	{
		// Parse incoming $args into an array and merge it with $defaults
		$args = wp_parse_args(
			$args,
			array(
				'webpage_id' => 0,
				'thread_id'  => 0,
				'assigned'   => 0,
			)
		);

		// get existing comments associated with image
		$comment_locations = get_post_meta((int) $args['webpage_id'], 'comment_locations', true);

		// add to or start a comment locations array
		if (isset($comment_locations) && is_array($comment_locations)) {
			$comment_locations[] = (int) $args['thread_id'];
		} else {
			$comment_locations = array((int) $args['thread_id']);
		}

		if ($args['assigned']) {
			update_post_meta((int) $args['thread_id'], 'assigned', (int) $args['assigned']);
		}

		// update locations
		update_post_meta((int) $args['webpage_id'], 'comment_locations', array_unique($comment_locations));
	}

	/**
	 * Updates page order in database
	 *
	 * @param int   $id Page id.
	 * @param array $thread_ids Thread ids.
	 *
	 * @return bool Success/Failure
	 */
	public function set_thread_order($id = 0, $thread_ids = array())
	{
		// get current pages.
		$threads = (array) get_post_meta($id, 'comment_locations', true);

		// return if empty.
		if (empty($threads)) {
			return false;
		}

		// order (flip to values, replace, then flip back).
		$threads_ordered = array_flip(array_replace(array_flip(array_map('intval', $thread_ids)), array_flip($threads)));

		// update order.
		update_post_meta($id, 'comment_locations', $threads_ordered);

		return true;
	}

	/**
	 * Sort images by menu order to keep order correct.
	 * Uses date as a fallback
	 *
	 * @param array $args Arguments for get request.
	 *
	 * @return array
	 */
	public function sort_by_menu_order($args)
	{
		$args['order_by'] = 'menu_order date';
		return $args;
	}

	public function load_current_page($response)
	{
		// get http referrer
		$referrer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '';
		$referrer = isset($_GET['ph_query_test']) ? $_GET['ph_query_test'] : $referrer;

		if ($referrer && isset($response->data) && isset($response->data['pages']) && !empty($response->data['pages'])) {
			foreach ($response->data['pages'] as $key => $page) {
				if (ph_normalize_url($referrer) === ph_normalize_url($page['page_url']) || ph_normalize_url($referrer, true) === ph_normalize_url($page['page_url'], true)) {
					$page['threads'] = PH()->website_thread->rest->fetch([
						'parent_id' => $page['id'],
						'per_page' => 500,
						"_expand" => [
							'all' => 'all'
						],
						'include_resolved' => false
					]);
					$response->data['pages'][$key] = $page;
				}
			}
		}

		return $response;
	}
}

function wp_124_disable_for_pages($load)
{
	global $post;
	// change 1,2,3,4,5 to post ids you want to exclude from commenting
	if (in_array($post->ID, [1, 2, 3, 4, 5])) {
		return false;
	}
	return $load;
}
add_filter('ph_script_should_start_loading', 'wp_124_disable_for_pages');
