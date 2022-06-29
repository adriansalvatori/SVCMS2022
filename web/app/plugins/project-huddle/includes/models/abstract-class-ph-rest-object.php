<?php

/**
 * Rest Object Class
 * Abstract class for our models to extend to create
 * internal API requests.
 *
 * For example, to get pages, you can use
 * PH()->page->rest->fetch(array(), array('per_page', 20) );
 *
 * @package ProjectHuddle
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Abstract rest object model class
 */
abstract class PH_Rest_Object
{
	/**
	 * Meta type
	 *
	 * @var string
	 */
	protected $meta_type = 'post';

	/**
	 * How to store collection name in model
	 *
	 * @var string
	 */
	public $collection_name = 'collection';

	/**
	 * Post type
	 *
	 * @var string
	 */
	public $post_type = 'post';

	/**
	 * Parent collection post type
	 *
	 * @var string
	 */
	public $parent_post_type = 'post';

	/**
	 * Project Type
	 *
	 * @var string
	 */
	public $endpoint_type = 'mockup';

	/**
	 * Post type route base
	 *
	 * @var string
	 */
	public $route_base = '/projecthuddle/v2';

	/**
	 * Post type route
	 *
	 * @var string
	 */
	public $route = '';

	/**
	 * Holds post type schema
	 *
	 * @var array
	 */
	public $schema = array();

	/**
	 * Model to use for getting model data
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Holds rest requests
	 *
	 * @var PH_Rest_Request
	 */
	public $rest;

	protected $rest_base = '';


	public function __construct()
	{
		$this->rest = new PH_Rest_Request($this->rest_base);
	}

	/**
	 * Get model by id
	 */
	public function get($id = 0, $autoload_post = true, $autoload_post_meta = true)
	{
		return new $this->model($id, $autoload_post, $autoload_post_meta);
	}

	public function fetch($wp_query_args = [], $autoload_post = true, $autoload_post_meta = true)
	{

		# default arguments
		$defaults = array(
			'posts_per_page' => -1,
			'post_type' => $this->post_type
		);

		# load default args
		$wp_query_args = wp_parse_args($wp_query_args, $defaults);

		$query = new \WP_Query($wp_query_args);

		$out = array();

		foreach ($query->posts as $post) {

			/**
			 * Note that $autoload_post here is forced to be false, since we know we have 
			 * a WP_Post in this case.
			 *
			 * This isn't totally necessary, but it's safe in case __construct() ever changes
			 * and somehow looks for this value
			 */
			$out[] = new $this->model($post, false, $autoload_post_meta);
		}

		return $out;
	}

	/**
	 * Get an array of new instances of this class (or an extension class) by field value or values
	 *
	 * This method allows us to get posts via WP_Query, while also passing in key/value pairs and a 'fields_relation'
	 * argument to the same array
	 *
	 * The net effect is that we can easily get extended posts, complete with postmeta, by field keys in a way
	 * that allows any arguments necessary from WP_Query
	 *
	 * If more control is needed over the meta_query item, you can
	 *
	 * 		- use self::get() (a more basic wrapper for WP_Query) and pass in the meta_query manually
	 * 		- use the 'ph_get_posts_by' hook to access the query arguments
	 *		- use a normal WP_Query or get_posts; and then for each $post, create a new Helping_Friendly_Post( $post )
	 *
	 * @param	array 	$args 	{
	 *
	 *		Arguments for getting posts.  Besides the keys given, any arguments for WP_Query can also be included.
	 *		
	 *		'fields_relation'
	 *		'fields' => array(
	 *			'meta_key_1' => 'value1', 
	 *			'meta_key_2' => array( 'value2', 'value3' ),
	 *			...
	 *		)
	 *	}
	 *
	 * @param 	bool	$autoload_post			Used when constructing the class instance
	 * @param 	bool	$autoload_post_meta		Used when constructing the class instance
	 *
	 * @return 	array
	 * @since 	1.0.0
	 */
	public static function fetch_by($args, $autoload_post = true, $autoload_post_meta = true)
	{

		# default arguments
		$defaults = array(
			'posts_per_page' => -1,
			'fields_relation' => 'OR',
		);

		# load default args
		$args = wp_parse_args($args, $defaults);

		# load the meta query

		## get key/value pairs from $args
		$meta_query = array();
		if (!empty($args['fields']))
			foreach ($args['fields'] as $k => $v) {

				# if the key is not in our default array, we'll consider it a post meta key
				if (!in_array($k, array_keys($defaults))) {

					# the new item we'll add to meta_query
					$new_meta_query_item = array('key' => $k, 'value' => $v);

					# if we have an array of values
					if (is_array($v)) $new_meta_query_item['compare'] = 'IN';
					else $new_meta_query_item['compare'] = '=';

					$meta_query[] = $new_meta_query_item;
				}
			} # end foreach: $args['fields']

		unset($args['fields']);

		## if keys were given, add the meta query to the wp query args
		if (!empty($meta_query)) {
			$meta_query['relation'] = $args['fields_relation'];
			$args['meta_query'] = $meta_query;
		}
		unset($args['fields_relation']);

		$args = apply_filters('ph_get_posts_by', $args);

		return $this->fetch($args, $autoload_post, $autoload_post_meta);
	} # get_by()

	/**
	 * Get rest item
	 *
	 * @param [type] ...$args
	 * @return void
	 */
	public function get_rest_item(...$args)
	{
		return $this->get_item(...$args);
	}

	/**
	 * Get rest items
	 *
	 * @param [type] ...$args
	 * @return void
	 */
	public function get_rest_items(...$args)
	{
		return $this->get_items(...$args);
	}

	public function schema()
	{
		return [];
	}

	/**
	 * Get object
	 *
	 * @param int    $id           ID of item.
	 * @param array  $params       Request Parameters.
	 * @param array  $query_params Request Query Parameters.
	 * @param string $route        Overwrite route.
	 *
	 * @return mixed
	 */
	public function get_item($id = 0, $params = array(), $query_params = array(), $route = '')
	{
		return $this->rest->get($id, $params, $query_params, $route);
	}

	/**
	 * Get object items
	 *
	 * @param array $params       Request Parameters.
	 * @param array $query_params Request Query Parameters.
	 *
	 * @return mixed
	 */
	public function get_items($params = array(), $query_params = array())
	{
		return $this->rest->fetch($params, $query_params);
	}

	/**
	 * Create item
	 *
	 * @param array  $params       Request Parameters.
	 * @param array  $query_params Request Query Parameters.
	 *
	 * @return mixed
	 */
	public function create_item($params = array(), $query_params = array(), $route = '')
	{
		return $this->rest->create($params, $query_params, $route);
	}

	/**
	 * Create item
	 *
	 * @param int    $id           Post id.
	 * @param array  $params       Request Parameters.
	 * @param array  $query_params Request Query Parameters.
	 * @param string $route        Overwrite route.
	 *
	 * @return mixed
	 */
	public function update_item($id = 0, $params = array(), $query_params = array(), $route = "")
	{
		return $this->rest->update($id, $params, $query_params, $route);
	}

	/**
	 * Method mapping for internal filters
	 *
	 * @param string $method Method type.
	 *
	 * @return string
	 */
	public function map_method($method)
	{
		switch ($method) {
			case 'POST':
				$method = 'new';
				break;
			case 'PUT':
			case 'PATCH':
				$method = 'edit';
				break;
			case 'DELETE':
				$method = 'delete';
				break;
			default:
				$method = 'get';
				break;
		}

		return $method;
	}

	/**
	 * Do internal API request
	 *
	 * @param WP_Rest_Request $request      Request.
	 * @param array           $params       Params.
	 * @param array           $query_params Query Params.
	 *
	 * @return mixed
	 */
	public function do_request($request, $params = array(), $query_params = array())
	{
		global $wp_rest_server;

		// Add specified request parameters into the request.
		if (!empty($params)) {
			foreach ($params as $param_name => $param_value) {
				$request->set_param($param_name, $param_value);
			}
		}

		// set query params (i.e. per_page, context, etc.).
		if (!empty($query_params)) {
			$request->set_query_params($query_params);
		}

		$response = rest_do_request($request);
		$data     = $wp_rest_server->response_to_data($response, true);

		return $data;
	}

	/**
	 * Add filters to type
	 */
	public function add_filters()
	{
		if ($this->parent_post_type) {
			// add collection data on get.
			add_filter("rest_prepare_{$this->parent_post_type}", array($this, 'collection_data'), 10, 3);
		}

		// add additional collection parameters.
		if ($this->collection_params()) {
			add_filter("rest_{$this->post_type}_collection_params", array($this, 'additional_collection_params'), 10, 2);
			add_filter("rest_{$this->post_type}_query", array($this, 'additional_collection_query'), 10, 2);
		}

		// convert everything to shortlinks to prevent 404.
		add_filter('rest_api_init', array($this, 'use_shortlinks'), 10, 2);
	}

	/**
	 * Get public collection query params.
	 *
	 * @param array  $query_params Query parameters.
	 * @param string $post_type    Post type.
	 *
	 * @return mixed
	 */
	public function additional_collection_params($query_params, $post_type)
	{
		foreach ((array) $this->collection_params() as $name => $data) {
			$query_params[$name] = $data;
		}

		return $query_params;
	}

	/**
	 * Add collection queries
	 * Each model can have query arguments for the collection
	 *
	 * @param array           $args    Query arguments.
	 * @param WP_Rest_Request $request Rest request.
	 *
	 * @return mixed
	 */
	public function additional_collection_query($args, $request)
	{
		foreach ((array) $this->collection_params() as $name => $data) {
			if ($data['meta']) {
				if (!isset($request[$name])) {
					return $args;
				}

				if (!isset($args['meta_query']) || !is_array($args['meta_query'])) {
					$args['meta_query'] = array();
				}

				if (isset($data['type'])) {
					$value = rest_sanitize_value_from_schema($request[$name], $data);
				} else {
					$value = $request[$name];
				}

				// if we're set to boolean, and false is requested, need to check if
				// meta does not exist or is false
				if ($data['type'] === 'boolean' && !$request[$name]) {
					$args['meta_query'][$name] = apply_filters(
						"ph_{$name}_collection_query_args",
						array(
							'relation' => 'AND',
							array(
								'relation' => 'OR',
								array(
									'key'   => $name,
									'value' => $value,
								),
								array(
									'key'     => $name,
									'compare' => 'NOT EXISTS',
								),
							),
						),
						$name,
						$request,
						$args
					);
				} else {
					$args['meta_query'][$name] = apply_filters(
						"ph_{$name}_collection_query_args",
						array(
							'key'   => $name,
							'value' => $value,
						),
						$name,
						$request,
						$args
					);
				}
			}
		}
		return $args;
	}

	/**
	 * Add parent model to collection parameters
	 *
	 * @param array  $query_params Query parameters.
	 * @param string $post_type    Post type.
	 *
	 * @return array
	 */
	public function parent_collection_param($query_params, $post_type)
	{
		$query_params['parent_id'] = array(
			'description' => __('Limit result set to items that have a parent model.'),
			'type'        => 'integer',
		);

		return $query_params;
	}

	public function project_name()
	{
		register_rest_field(
			$this->post_type,
			'project_name',
			array(
				'update_callback' => null,
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					// get/set in transient
					$cache = get_transient("ph_{$this->action_slug}_{$attr}_{$post['id']}");
					if (false === $cache) {
						$parents 	= ph_get_parents_ids($post['id']);
						$id 		= $parents['project'];
						$cache 		= ph_get_the_title($id);
						// cache forever
						set_transient("ph_{$this->action_slug}_{$attr}_{$post['id']}", $cache, 0);
					}

					return $cache ? $cache : __('Untitled', 'project-huddle');
				},
				'schema'          => array(
					'description' => esc_html__('ID of the project.', 'project-huddle'),
					'type'        => 'integer',
					'default'     => 0,
					'readonly'    => true,
				),
			)
		);
	}

	public function item_name()
	{
		register_rest_field(
			$this->post_type,
			'item_name',
			array(
				'update_callback' => null,
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					// get/set in transient
					$cache = get_transient("ph_{$this->action_slug}_{$attr}_{$post['id']}");
					if (false === $cache) {
						$parents 	= ph_get_parents_ids($post['id']);
						$id 		= $parents['item'];
						$cache 		= ph_get_the_title($id);
						// cache forever
						set_transient("ph_{$this->action_slug}_{$attr}_{$post['id']}", $cache, 0);
					}

					return $cache ? $cache : __('Untitled', 'project-huddle');
				},
				'schema'          => array(
					'description' => esc_html__('ID of the project.', 'project-huddle'),
					'type'        => 'integer',
					'default'     => 0,
					'readonly'    => true,
				),
			)
		);
	}


	public function project_id()
	{
		register_rest_field(
			$this->post_type,
			'project_id',
			array(
				'update_callback' => null,
				'get_callback'    => function ($post, $attr, $request, $object_type) {
					return (int) get_post_meta($post['id'], $attr, true);
				},
				'schema'          => array(
					'description' => esc_html__('ID of the project.', 'project-huddle'),
					'type'        => 'integer',
					'default'     => 0,
					'readonly'    => true,
				),
			)
		);
	}

	/**
	 * Add child collection data to response
	 *
	 * @param WP_Rest_Response $response Modified response.
	 * @param WP_Post          $post     Post object.
	 *
	 * @return false|object
	 */
	public function collection_data($response, $post, $request)
	{
		$expanded = (array) $request['_expand'];

		if (!empty($expanded) && (array_key_exists($this->collection_name, $expanded) || array_key_exists('all', $expanded))) {
			// if all
			if (isset($expanded['all'])) {
				$per_page = $expanded['all'] == 'all' ? 500 : $expanded['all'];
			} elseif (isset($expanded[$this->collection_name])) {
				$per_page = $expanded[$this->collection_name] == 'all' ? 500 : $expanded[$this->collection_name];
			}

			// regular params
			$args = apply_filters(
				"ph_{$this->collection_name}_collection_data",
				array(),
				$post,
				$request
			);

			$query_params = apply_filters(
				"ph_{$this->collection_name}_collection_query_params",
				array(
					'per_page'  => $per_page ? (int) $per_page : 10,
					'order'     => 'asc',
					'orderby'   => 'date',
					'_expand'   => $request['_expand'],
					"_signature" => $request['_signature'],
					'parent_id' => $post->ID,
				),
				$post,
				$request
			);

			$response->data[$this->collection_name] = $this->rest->fetch($args, $query_params);
		}

		return apply_filters("ph_{$this->collection_name}_collection_response", $response);
	}



	/**
	 * Register our custom fields with API
	 */
	public function register_fields_from_schema()
	{
		if (empty($this->schema)) {
			return;
		}
		foreach ($this->schema as $key => $property) {
			$meta = array(
				'type'         => $property['type'], // this will sanitize our type.
				'description'  => $property['description'],
				'single'       => true,
				// 'show_in_rest' => true,
			);

			// optional/additional sanitize callback.
			if (array_key_exists('arg_options', $property) && array_key_exists('sanitize_callback', $property['arg_options'])) {
				$meta['sanitize_callback'] = $property['arg_options']['sanitize_callback'];
			}

			register_meta($this->post_type, $key, $meta);
		}
	}

	/**
	 * Add additional fields to rest object
	 */
	public function rest_fields_from_schema()
	{
		if (empty($this->schema)) {
			return;
		}

		foreach ($this->schema as $key => $property) {
			$this->property = $property;

			// if we need to run a custom rest function.
			if (!isset($property['custom'])) {
				$this->register_meta_rest_field($key, $property);
			}
		}
	}

	/**
	 * Register meta rest fields
	 *
	 * @param string $key      Model key.
	 * @param string $property Model property.
	 */
	public function register_meta_rest_field($key, $property)
	{
		$endpoint_type = $this->endpoint_type;
		$action_slug   = $this->action_slug;

		// register each rest field.
		register_rest_field(
			$this->post_type,
			$key,
			array(
				'get_callback'    => function ($post, $attr, $request, $object_type) use ($property, $endpoint_type, $action_slug) {
					$data = false;

					if (metadata_exists($this->meta_type, $post['id'], $attr)) {
						$data = get_metadata($this->meta_type, $post['id'], $attr, true);
						$data = apply_filters("ph_{$endpoint_type}_rest_get_{$action_slug}_attribute", $data, $attr, $post);
					} else if (isset($property['default'])) {
						$data = $property['default'];
					}

					return rest_sanitize_value_from_schema($data, $property);
				},
				'update_callback' => function ($value, $post, $attr, $request, $object_type) use ($property, $endpoint_type, $action_slug) {
					global $is_ph_batch;
					// add permissions filter for granular control.
					if (!apply_filters("ph_{$endpoint_type}_update_{$action_slug}_{$attr}_allowed", true, $post, $value)) {
						return new WP_Error('rest_forbidden_context', __('Sorry, you are not allowed to do this.', 'project-huddle'), array('status' => rest_authorization_required_code()));
					}

					// run pre action on update
					do_action("ph_{$endpoint_type}_pre_rest_update_{$action_slug}_attribute", $attr, $value, $post);

					// update.
					$updated = update_metadata($this->meta_type, $post->ID, $attr, $value);

					// run action on update.
					if ($updated) {
						if (is_int($updated)) {
							do_action("ph_{$endpoint_type}_rest_create_{$action_slug}_attribute", $attr, $value, $post, $is_ph_batch);
						} else {
							do_action("ph_{$endpoint_type}_rest_update_{$action_slug}_attribute", $attr, $value, $post, $is_ph_batch);
						}
					}

					// sanitize.
					return rest_sanitize_value_from_schema($value, $property);
				},
				'schema'          => $property,
			)
		);
	}

	/**
	 * Add short link to model attribute.
	 *
	 * @return void
	 */
	public function use_shortlinks()
	{
		register_rest_field(
			$this->post_type,
			'ph_short_link',
			array(
				'get_callback'    => array($this, 'get_shortlink'),
				'update_callback' => null,
				'schema'          => array(
					'description' => 'Short Link',
					'type'        => 'string',
					'readonly'    => true,
					'context'     => array('view'),
				),
			)
		);
	}

	/**
	 * Return a shortlink
	 *
	 * @param array $post Post array.
	 *
	 * @return string
	 */
	public function get_shortlink($post)
	{
		return wp_get_shortlink((int) $post['id']);
	}

	/**
	 * Collection parameters
	 *
	 * @return array
	 */
	public function collection_params()
	{
		return array();
	}

	/**
	 * Maybe trash post if parent is trashed
	 *
	 * @return void
	 */
	function maybe_trash($post_id)
	{
		// if it's not a parent post, bail
		if (get_post_type($post_id) !== $this->parent_post_type) {
			return;
		}

		// get this posts children
		$children = new WP_Query(
			array(
				'post_type'   => $this->post_type,
				'post_status' => 'publish',
				'fields'      => 'ids', // cheaper
				'meta_key'   => 'parent_id',
				'posts_per_page' => -1,
				'meta_value' => $post_id
			)
		);


		// if there are children, trash them too
		if ($children->posts) {
			foreach ($children->posts as $id) {
				wp_trash_post($id);
			}
		}

		// Restore original Post Data
		wp_reset_postdata();
	}

	function maybe_untrash($post_id)
	{
		// if it's not a parent post, bail
		if (get_post_type($post_id) !== $this->parent_post_type) {
			return;
		}

		// get this posts children
		$children = new WP_Query(
			array(
				'post_type'   => $this->post_type,
				'post_status' => array('trash'),
				'fields'      => 'ids', // cheaper
				'meta_key'   => 'parent_id',
				'posts_per_page' => -1,
				'meta_value' => $post_id
			)
		);

		// if there are children, trash them too
		if ($children->posts) {
			foreach ($children->posts as $id) {
				wp_untrash_post($id);
			}
		}

		// Restore original Post Data
		wp_reset_postdata();
	}

	/**
	 * Standardize actions for each model
	 * I.E. Threads:
	 *
	 * PHP objects Fires every time
	 * ph_website_publish_thread
	 * ph_website_edit_thread
	 * ph_website_delete_thread
	 *
	 * REST actions fire only for rest actions.
	 * This is useful for instance if you want to do an action
	 * for only rest requests but not for internal changes to post types
	 *
	 * ph_website_rest_publish_thread
	 * ph_website_rest_edit_thread
	 * ph_website_rest_delete_thread
	 */
	public function add_actions()
	{
		// regular actions.
		add_action("publish_{$this->post_type}", array($this, 'new_action'), 10, 2);
		add_action("edit_{$this->post_type}", array($this, 'edit_action'), 10, 2);
		add_action("trashed_{$this->post_type}", array($this, 'delete_action'), 10, 2);

		// rest actions.
		add_filter("rest_prepare_{$this->post_type}", array($this, 'handle_rest_prepare'), 10, 3);
		add_action("rest_insert_{$this->post_type}", array($this, 'edit_rest_action'), 20, 3);
		add_action("rest_delete_{$this->post_type}", array($this, 'delete_rest_action'), 20, 3);
	}

	/**
	 * New thread action
	 *
	 * @param int        $id   The Post ID.
	 * @param WP_Comment $item Post object.
	 */
	public function new_action($id, $item)
	{
		do_action("ph_{$this->endpoint_type}_publish_{$this->action_slug}", $id, $item);
	}

	/**
	 * Edit Filter
	 *
	 * @param int   $id   The Post ID.
	 * @param array $item Post Object.
	 */
	public function edit_action($id, $item)
	{
		do_action("ph_{$this->endpoint_type}_edit_{$this->action_slug}", $id, $item);
	}

	/**
	 * Edit Filter
	 *
	 * @param int   $id   The Post ID.
	 * @param array $item Post Object.
	 */
	public function delete_action($id, $item)
	{
		do_action("ph_{$this->endpoint_type}_delete_{$this->action_slug}", $id, $item);
	}

	/**
	 * Need to do a special case for POST since meta are added after
	 * rest_insert_post
	 *
	 * @param WP_Rest_Response $response Response.
	 * @param mixed            $object   Post or Comment Object.
	 * @param WP_Rest_Request  $request  Rest request.
	 *
	 * @return mixed
	 */
	public function handle_rest_prepare($response, $object, $request)
	{
		if ($request->get_method() === 'POST') {
			do_action("ph_{$this->endpoint_type}_rest_publish_{$this->action_slug}", $object, $request);
		}

		return $response;
	}

	/**
	 * Fires after a single post object is updated via the REST API.
	 *
	 * @param WP_Post         $post     Inserted or updated post object.
	 * @param WP_REST_Request $request  Request object.
	 * @param bool            $creating True when creating a post, false when updating.
	 */
	public function edit_rest_action($post, $request, $creating)
	{
		// must not be new.
		if ($creating) {
			return;
		}

		do_action("ph_{$this->endpoint_type}_rest_edit_{$this->action_slug}", $post, $request);
	}

	/**
	 * Fires after a single post object is updated via the REST API.
	 *
	 * @param object           $post     The deleted or trashed post.
	 * @param WP_REST_Response $response The response data.
	 * @param WP_REST_Request  $request  The request sent to the API.
	 */
	public function delete_rest_action($post, $response, $request)
	{
		do_action("ph_{$this->endpoint_type}_rest_delete_{$this->action_slug}", $post, $response, $request);
	}
}
